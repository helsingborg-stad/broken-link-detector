<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Config;

interface FeatureInterface
{
    public function isEnabled(?int $version): bool;

    public function getVersion(): int|false;

    public static function factory(string $feature): FeatureInterface;
}
