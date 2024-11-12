<?php

namespace BrokenLinkDetector\Asset;

use BrokenLinkDetector\Asset\AssetRegistry;

class ContextDetection extends AssetRegistry implements AssetInterface
{
  public function getHook(): string
  {
    return 'wp_enqueue_scripts';
  }

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
      'isEnabled' => $this->config->isContextCheckEnabled(),
      'checkUrl' => $this->config->getContextCheckUrl(),
      'checkTimeout' => $this->config->getContextCheckTimeout(),
      'domains' => $this->config->getContextCheckDomainsToDisable(),
      'tooltip' => $this->config->getContextCheckTooltipText(),
      'successClass' => $this->config->getContextCheckSuccessClass(),
      'failedClass' => $this->config->getContextCheckFailedClass(),
    ];
  }
}