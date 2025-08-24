<?php

declare(strict_types=1);

namespace SimpleMVC\Compiler;

interface CompilerPassInterface
{
    /**
     * Process and compile data for caching.
     * 
     * @param string $cacheDir Directory to write cache files.
     * @return void
     */
    public function process(string $cacheDir): void;
}