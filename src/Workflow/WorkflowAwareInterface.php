<?php

namespace SimpleMVC\Workflow;

interface WorkflowAwareInterface
{
    public function getWorkflowName(): string;
    public function getId(): ?int;
}