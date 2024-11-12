<?php 

namespace BrokenLinkDetector\Config;

use InvalidArgumentException;

class Feature implements FeatureInterface {

  private const FEATURES = [
    //General Features
    'installer' => 1,
    'language' => 1,
    'field_loader' => 1,

    //Admin Features
    'admin_settings' => 1,
    'admin_summary' => 1,
    'admin_highlight_links' => 1,

    //On save autofixer
    'fix_internal_links' => 1,

    //CLI Features
    'cli' => 1,
    'cli_installer' => 1,
    'cli_link_finder' => 1,
    'cli_link_classifier' => 1,

    //Frontend context detection
    'context_detection' => 1,
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