<?php

namespace SimpleMVC\Workflow;

interface GuardInterface
{
    public function check(object $entity): bool;
}