<?php

namespace SimpleMVC\Templating\Twig;

interface TwigFunctionInterface
{
    public function getFunctions(): array;
    
    /**
     * Indicates if this extension uses static methods
     */
    public function isStatic(): bool;
}
