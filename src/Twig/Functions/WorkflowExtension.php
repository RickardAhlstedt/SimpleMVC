<?php

namespace SimpleMVC\Twig\Functions;

use SimpleMVC\Templating\Twig\TwigFunctionInterface;

class WorkflowExtension implements TwigFunctionInterface
{
    public function isStatic(): bool
    {
        return false;
    }

    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('workflow_state', [$this, 'getWorkflowState']),
            new \Twig\TwigFunction('workflow_can', [$this, 'getWorkflowCan']),
        ];
    }

    public function getWorkflowState(object $entity): string
    {
        $container = \SimpleMVC\Core\Container::getInstance();
        $workflowManager = $container->get(\SimpleMVC\Workflow\WorkflowManager::class);
        
        // Check if entity implements WorkflowAwareInterface
        if (!$entity instanceof \SimpleMVC\Workflow\WorkflowAwareInterface) {
            throw new \InvalidArgumentException('Entity must implement WorkflowAwareInterface');
        }
        
        return $workflowManager->getCurrentState($entity);
    }

    public function getWorkflowCan(object $entity, string $transitionName): bool
    {
        $container = \SimpleMVC\Core\Container::getInstance();
        $workflowManager = $container->get(\SimpleMVC\Workflow\WorkflowManager::class);
        
        if (!$entity instanceof \SimpleMVC\Workflow\WorkflowAwareInterface) {
            throw new \InvalidArgumentException('Entity must implement WorkflowAwareInterface');
        }
        
        return $workflowManager->can($entity, $transitionName);
    }
}
