<?php

namespace SimpleMVC\Debug;

interface DataCollectorInterface
{
    public function getName(): string;
    public function collect(): array;
    public function getIcon(): string;
    public function getLabel(): string;
    public function getPriority(): int;
}