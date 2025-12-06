<?php
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('asset', [$this, 'assetPath']),
        ];
    }

    public function assetPath(string $path): string
    {
        // Préfixe avec la racine publique
        return '/' . ltrim($path, '/');
    }
}
