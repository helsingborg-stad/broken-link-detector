<?php

namespace BrokenLinkDetector\Config;

interface FeatureInterface 
{
    public function isEnabled(): bool;
    public function getVersion(): int|false;
    public static function factory(string $feature): FeatureInterface;
}