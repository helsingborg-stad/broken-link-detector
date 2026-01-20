<?php

declare(strict_types=1);

namespace BrokenLinkDetector\BrokenLinkRegistry\Link;

use BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink\Classify;
use BrokenLinkDetector\Config\Config;
use WpService\WpService;

interface LinkInterface
{
    public static function createLink(string $url, int $httpCode, int $postId, WpService $wpService, Config $config): Link;

    public function classify(): Classify;
}
