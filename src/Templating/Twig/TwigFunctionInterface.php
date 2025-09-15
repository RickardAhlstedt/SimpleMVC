<?php

namespace SimpleMVC\Templating\Twig;

use Twig\TwigFunction;

interface TwigFunctionInterface
{
    public function getFunctions(): array;
}
