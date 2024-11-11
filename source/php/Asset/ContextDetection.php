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
    return 'js/context-detector.js';
  }

  public function getLocalizeData(): ?array
  {
    return [
      'domains' => $this->config->getDomainsThatShouldNotBeChecked(),
      'tooltip' => $this->config->getTooltipText(),
    ];
  }
}