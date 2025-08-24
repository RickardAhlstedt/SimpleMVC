<?php

declare(strict_types=1);

namespace SimpleMVC\Compiler;

class Compiler
{
    /**
     * @var CompilerPassInterface[]
     */
    private array $passes = [];

    public function addPass(CompilerPassInterface $pass): void
    {
        $this->passes[] = $pass;
    }

    public function compile(string $cacheDir): void
    {
        foreach ($this->passes as $pass) {
            $pass->process($cacheDir);
        }
    }

    /**
     * Recursively replace placeholders in config array.
     */
    public static function replacePlaceholders($data, array $vars)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::replacePlaceholders($value, $vars);
            }
            return $data;
        }
        if (is_string($data)) {
            return strtr($data, $vars);
        }
        return $data;
    }
}