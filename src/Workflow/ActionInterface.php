<?php

namespace SimpleMVC\Workflow;

interface ActionInterface
{
    public function execute(object $entity, TransitionInterface $transition): void;
}