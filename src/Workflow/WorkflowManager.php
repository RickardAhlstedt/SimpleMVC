<?php

namespace SimpleMVC\Workflow;

use SimpleMVC\Database\EntityManager;

class WorkflowManager
{
    private array $workflows = [];
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addWorkflow(WorkflowInterface $workflow): void
    {
        $this->workflows[$workflow->getName()] = $workflow;
    }

    public function can(WorkflowAwareInterface $entity, string $transitionName): bool
    {
        $workflow = $this->workflows[$entity->getWorkflowName()] ?? null;
        if (!$workflow) return false;

        $currentState = $this->getCurrentState($entity);
        $transition = $this->findTransition($workflow, $transitionName);
        
        if (!$transition || !in_array($currentState, $transition->getFrom())) {
            return false;
        }

        // Check guards
        foreach ($transition->getGuards() as $guard) {
            if (!$guard->check($entity)) {
                return false;
            }
        }

        return true;
    }

    public function apply(WorkflowAwareInterface $entity, string $transitionName): void
    {
        if (!$this->can($entity, $transitionName)) {
            throw new \Exception("Transition '{$transitionName}' not allowed");
        }

        $workflow = $this->workflows[$entity->getWorkflowName()];
        $transition = $this->findTransition($workflow, $transitionName);

        // Execute actions
        foreach ($transition->getActions() as $action) {
            $action->execute($entity, $transition);
        }

        // Update state in database
        $this->setState($entity, $transition->getTo());
    }

    public function getCurrentState(WorkflowAwareInterface $entity): string
    {
        $stmt = $this->entityManager->getDriver()->query(
            "SELECT state FROM workflow_states WHERE class = ? AND class_id = ? AND workflow_name = ?",
            [get_class($entity), $entity->getId(), $entity->getWorkflowName()]
        );
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['state'];
        }

        // Return initial state if no record exists
        $workflow = $this->workflows[$entity->getWorkflowName()];
        $initialState = $workflow->getInitialState();
        
        // Create initial record
        $this->setState($entity, $initialState);
        
        return $initialState;
    }

    private function setState(WorkflowAwareInterface $entity, string $state): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Try to update existing record first
        $stmt = $this->entityManager->getDriver()->query(
            "UPDATE workflow_states SET state = ?, updated_at = ? WHERE class = ? AND class_id = ? AND workflow_name = ?",
            [$state, $now, get_class($entity), $entity->getId(), $entity->getWorkflowName()]
        );

        // If no rows affected, insert new record
        if ($stmt->rowCount() === 0) {
            $this->entityManager->getDriver()->query(
                "INSERT INTO workflow_states (class, class_id, state, workflow_name, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)",
                [get_class($entity), $entity->getId(), $state, $entity->getWorkflowName(), $now, $now]
            );
        }
    }

    public function getStateHistory(WorkflowAwareInterface $entity): array
    {
        // This would require a separate workflow_transitions table for full history
        $stmt = $this->entityManager->getDriver()->query(
            "SELECT * FROM workflow_states WHERE class = ? AND class_id = ? AND workflow_name = ? ORDER BY created_at DESC",
            [get_class($entity), $entity->getId(), $entity->getWorkflowName()]
        );
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function findTransition(WorkflowInterface $workflow, string $name): ?TransitionInterface
    {
        $transitions = $workflow->getTransitions();
        return $transitions[$name] ?? null;
    }
}