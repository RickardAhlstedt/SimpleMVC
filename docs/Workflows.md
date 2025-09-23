# Workflows

SimpleMVC includes a powerful workflow system for managing entity state transitions with built-in guards, actions, and database persistence.

## Overview

The workflow system allows you to define state machines for your entities, ensuring that state changes follow predefined rules and can trigger custom logic.

**Key Features:**
- Database-backed state persistence
- Guard conditions for transition validation
- Custom actions executed during transitions
- Twig integration for templates
- Full audit trail of state changes

## Basic Usage

### 1. Make Your Entity Workflow-Aware

```php
<?php

namespace App\Entities;

use SimpleMVC\Workflow\WorkflowAwareInterface;

class Post implements WorkflowAwareInterface
{
    public int $id;
    public string $title;
    public string $content;

    public function getWorkflowName(): string
    {
        return 'post_publishing';
    }

    public function getId(): int
    {
        return $this->id;
    }
}
```

### 2. Define Your Workflow

```php
<?php

namespace App\Workflow;

use SimpleMVC\Workflow\WorkflowInterface;
use SimpleMVC\Workflow\Transition;

class PostWorkflow implements WorkflowInterface
{
    public function getName(): string
    {
        return 'post_publishing';
    }

    public function getStates(): array
    {
        return [
            'draft' => ['type' => 'initial'],
            'review' => ['type' => 'normal'],
            'published' => ['type' => 'normal'],
            'archived' => ['type' => 'final']
        ];
    }

    public function getTransitions(): array
    {
        return [
            'submit_for_review' => new Transition(
                'submit_for_review',
                ['draft'],
                'review'
            ),
            'publish' => new Transition(
                'publish',
                ['review'],
                'published'
            ),
            'archive' => new Transition(
                'archive',
                ['published'],
                'archived'
            )
        ];
    }

    public function getInitialState(): string
    {
        return 'draft';
    }
}
```

3. Register and use the workflow

```php
$post = $entityManager->find(Post::class, 1);

// Check current state
$currentState = $workflowManager->getCurrentState($post);

// Check if transition is allowed
if ($workflowManager->can($post, 'submit_for_review')) {
    // Apply the transition
    $workflowManager->apply($post, 'submit_for_review');
}
```

## Guards and Actions

### Guards

Guards are conditions that must be met for a transition to be allowed:

```php
<?php

namespace App\Workflow\Guards;

use SimpleMVC\Workflow\GuardInterface;

class AdminGuard implements GuardInterface
{
    public function check(object $entity): bool
    {
        return $_SESSION['user_role'] === 'admin';
    }
}
```

### Actions

Actions are executed when a transition occurs:

```php
<?php

namespace App\Workflow\Actions;

use SimpleMVC\Workflow\ActionInterface;
use SimpleMVC\Workflow\TransitionInterface;

class PublishAction implements ActionInterface
{
    public function execute(object $entity, TransitionInterface $transition): void
    {
        $entity->published_at = new \DateTime();
        // Send notifications, update search index, etc.
    }
}
```

### Using guards and actions

```php
<?php
public function getTransitions(): array
{
    return [
        'publish' => new Transition(
            'publish',
            ['review'],
            'published',
            [new AdminGuard()],           // Guards
            [new PublishAction()]         // Actions
        ),
    ];
}
```

## Database setup

The table is setup as followed:

- class - Entity class name
- class_id - Entity ID
- state - Current state
- workflow_name - Workflow identifier
- created_at / updated_at - Timestamps

## Twig integration

Use workflow functions in your templates:

```php
{# Check current state #}
<p>Status: {{ workflow_status(post) }}</p>

{# Conditional buttons based on allowed transitions #}
{% if workflow_can(post, 'submit_for_review') %}
    <form method="post" action="/posts/{{ post.id }}/submit">
        <button type="submit">Submit for Review</button>
    </form>
{% endif %}

{% if workflow_can(post, 'publish') %}
    <form method="post" action="/posts/{{ post.id }}/publish">
        <button type="submit">Publish</button>
    </form>
{% endif %}

{% if workflow_can(post, 'archive') %}
    <form method="post" action="/posts/{{ post.id }}/archive">
        <button type="submit">Archive</button>
    </form>
{% endif %}
```

## Controller integration

Handle workflow transitions in your controllers:

```php
<?php
#[Route(name: 'post_transition', path: '/posts/{id}/{transition}', method: 'POST')]
public function transition(int $id, string $transition, EntityManager $em, WorkflowManager $workflow): Response
{
    $post = $em->find(Post::class, $id);
    
    if (!$post) {
        throw new \Exception('Post not found');
    }

    if (!$workflow->can($post, $transition)) {
        throw new \Exception('Transition not allowed');
    }

    $workflow->apply($post, $transition);
    
    return new Response('Transition applied successfully');
}
```

## Advanced features

### State History

Get the complete state history for an entity:

```php
<?php
$history = $workflowManager->getStateHistory($post);
foreach ($history as $record) {
    echo "State: {$record['state']} at {$record['created_at']}\n";
}
```

### Multiple Workflows

Entities can have multiple workflows by implementing different workflow names:

```php
<?php
public function getWorkflowName(): string
{
    return match($this->type) {
        'article' => 'article_workflow',
        'page' => 'page_workflow',
        default => 'default_workflow'
    };
}
```

### Custom State Types

Define different state types in your workflow:

```php
<?php
public function getStates(): array
{
    return [
        'draft' => ['type' => 'initial'],
        'published' => ['type' => 'normal'],
        'featured' => ['type' => 'special'],
        'archived' => ['type' => 'final']
    ];
}
```

## Best practices

1. Keep workflows simple - Complex business logic should be in actions, not guards
2. Use descriptive names - State and transition names should be self-explaining
3. Test transitions - Write unit tests for your workflow logic
4. Log important transitions - Use actions to log significant state changes
5. Handle errors gracefully - Always check can() before apply()
