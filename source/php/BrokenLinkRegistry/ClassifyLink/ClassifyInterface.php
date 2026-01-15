<?php

declare(strict_types=1);

namespace BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink;

use BrokenLinkDetector\Config\Config;
use WpService\WpService;

interface ClassifyInterface
{
    public function isInternal(): bool;

    public function isExternal(): bool;

    public function isBroken(): ?bool; //Null if http code is not provided

    public function getHttpCode(): ?int; //Null if http code is not provided

    public static function factory(string $url, ?int $httpCode, WpService $wpService, Config $config): Classify;
}
