<?php

declare(strict_types=1);

namespace SimpleMVC\Templating\Twig;

interface TwigGlobalInterface
{
    public function getName(): string;
    public function getValue();
}
