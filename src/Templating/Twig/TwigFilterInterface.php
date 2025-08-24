<?php

namespace SimpleMVC\Templating\Twig;

use Twig\TwigFilter;

interface TwigFilterInterface
{
    public function getFilter(): TwigFilter;
}