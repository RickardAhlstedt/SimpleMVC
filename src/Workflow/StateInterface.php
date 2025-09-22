<?php

namespace SimpleMVC\Workflow;

interface StateInterface
{
    public function getName(): string;
    public function getType(): string;
}