<?php 

namespace BrokenLinkDetector\Detect;

use WpService\WpService;

interface ClassifyInterface
{
    public function isInternal(): bool;
    public function isExternal(): bool;
    public static function factory(string $url, WpService $wpService): Classify;
}