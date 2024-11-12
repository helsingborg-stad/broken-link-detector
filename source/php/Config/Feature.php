<?php 

namespace BrokenLinkDetector\Config;

use InvalidArgumentException;

class Feature implements FeatureInterface {

  private const FEATURES = [
    'cli' => 1,
    'installer' => 1,
    'language' => 1,
    'admin_settings' => 1,
    'field_loader' => 1,
    'scan_broken_links' => 1,
    'list_broken_links' => 1,
    'fix_internal_links' => 1,
    'highlight_broken_links' => 1,
    'link_finder' => 1,
    'classify_links' => 1,
    'context_detection' => 1,
    'admin_summary' => 1,
  ];

  private function __construct(private string $feature) {
    if (!isset(self::FEATURES[$feature])) {
      throw new InvalidArgumentException("Feature {$feature} is not supported.");
    }
  }

  public function isEnabled(): bool 
  {
    $isEnabled = self::FEATURES[$this->feature];
    return $isEnabled ? true : false;
  }

  public function getVersion(): int|false {
    return self::FEATURES[$this->feature] ?: false;
  }

  public static function factory(string $feature): Feature {
    return new self($feature);
  }
}