<?php

namespace BrokenLinkDetector\Asset;

use BrokenLinkDetector\Asset\AssetRegistry;

class FrontendStyles extends AssetRegistry implements AssetInterface
{
  public function getHook(): string
  {
    return 'wp_enqueue_scripts';
  }

  public function getHandle(): string
  {
    return 'broken-link-context-detection';
  }

  public function getDependencies(): array
  {
    return [];
  }

  public function getFilename(): string
  {
    return 'css/broken-link-detector.css';
  }

  public function getLocalizeData(): ?array
  {
    return null;
  }
}