<?php

namespace BrokenLinkDetector\Asset;

use BrokenLinkDetector\Asset\AssetRegistry;

class EditorHighlight extends AssetRegistry implements AssetInterface
{
  public function getHook(): string
  {
    return 'mce_external_plugins';
  }

  public function getHandle(): string
  {
    return 'broken-link-editor-highlight';
  }

  public function getFilename(): string
  {
    return 'js/editor-highlight.js';
  }

  public function getLocalizeData(): ?array
  {
    return [
    ];
  }
}