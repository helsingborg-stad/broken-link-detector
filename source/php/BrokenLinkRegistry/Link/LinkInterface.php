<?php

namespace BrokenLinkDetector\BrokenLinkRegistry\Link;

use WpService\WpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink\Classify;

interface LinkInterface
{
    public static function createLink(string $url, int $httpCode, int $postId, WpService $wpService, Config $config): Link;
    public function classify(): Classify;
}