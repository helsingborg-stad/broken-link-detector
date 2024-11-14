<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink;

use WpService\WpService;
use BrokenLinkDetector\Config\Config;

interface ClassifyInterface
{
    public function isInternal(): bool;
    public function isExternal(): bool;
    public function isBroken(): ?bool; //Null if http code is not provided
    public function getHttpCode(): ?int; //Null if http code is not provided
    public static function factory(string $url, ?int $httpCode, WpService $wpService, Config $config): Classify;
}