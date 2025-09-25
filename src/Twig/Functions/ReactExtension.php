<?php

namespace SimpleMVC\Twig\Functions;

use SimpleMVC\Templating\Twig\TwigFunctionInterface;
use Twig\TwigFunction;

class ReactExtension implements TwigFunctionInterface
{
    public function isStatic(): bool
    {
        return false;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('react_component', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    public function render(string $name, array $props = [], ?string $id = null, string $tag = 'div'): string
    {
        $id = $id ?: 'react-' . bin2hex(random_bytes(6));
        $json = json_encode($props, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        return sprintf(
            '<%1$s id="%2$s" data-react-component="%3$s" data-props="%4$s"></%1$s>',
            htmlspecialchars($tag, ENT_QUOTES),
            htmlspecialchars($id, ENT_QUOTES),
            htmlspecialchars($name, ENT_QUOTES),
            htmlspecialchars($json, ENT_QUOTES)
        );
    }
}