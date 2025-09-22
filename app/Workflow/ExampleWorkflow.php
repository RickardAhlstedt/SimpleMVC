<?php

namespace App\Workflow;

use SimpleMVC\Workflow\WorkflowInterface;
use SimpleMVC\Workflow\Transition;

class ExampleWorkflow implements WorkflowInterface
{
    public function getName(): string
    {
        return 'example_workflow';
    }

    public function getStates(): array
    {
        return [
            'in_review' => ['type' => 'initial'],
            'review' => ['type' => 'normal'],
            'approved' => ['type' => 'final'],
            'rejected' => ['type' => 'final'],
        ];
    }

    public function getTransitions(): array
    {
        return [
            'submit_review' => new Transition(
                'submit_review',
                ['in_review'],
                'review'
            ),
            'approve' => new Transition(
                'approve',
                ['review'],
                'approved'
            ),
            'reject' => new Transition(
                'reject',
                ['review'],
                'rejected'
            ),
        ];
    }

    public function getInitialState(): string
    {
        return 'in_review';
    }
}
