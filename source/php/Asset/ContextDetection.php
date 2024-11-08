<?php

namespace BrokenLinkDetector\Asset;

use BrokenLinkDetector\Asset\AssetRegistry;

class ContextDetection extends AssetRegistry implements AssetInterface
{

  public function getHandle(): string
  {
    return 'broken-link-context-detection';
  }

  public function getFilename(): string
  {
    return 'js/context-detection.js';
  }

  public function getLocalizeData(): ?array
  {
    return [
      'foo' => 'bar',
      'domains' => $this->config->getDomainsThatShouldNotBeChecked()
    ];
  }
}