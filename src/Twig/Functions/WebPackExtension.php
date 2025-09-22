<?php

namespace SimpleMVC\Twig\Functions;

use SimpleMVC\Templating\Twig\TwigFunctionInterface;
use SimpleMVC\Services\LoggerService;

class WebPackExtension implements TwigFunctionInterface
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('webpack', [$this, 'getAssetPath']),
        ];
    }

    public function getAssetPath(string $assetName): string
    {
        $manifestPath = PATH_ROOT . '/public/assets/manifest.json';
        if (!file_exists($manifestPath)) {
            throw new \RuntimeException("Asset manifest file not found: " . $manifestPath);
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Error decoding asset manifest JSON: " . json_last_error_msg());
        }

        if (!isset($manifest[$assetName])) {
            if(isset($manifest['files'][$assetName])) {
                return $manifest['files'][$assetName];
            }
            throw new \InvalidArgumentException("Asset not found in manifest: " . $assetName . ". Available assets: " . implode(", ", array_keys($manifest)));
        }

        return $manifest[$assetName];
    }
}
