<?php

namespace BrokenLinkDetector\Asset;

use BrokenLinkDetector\Asset\AssetRegistry;

class EditorHighlight extends AssetRegistry implements AssetInterface
{
  public function shouldEnqueue(): bool
  {
    return true;
  }

  public function getHook(): string
  {
    return 'mce_external_plugins';
  }

  public function getHandle(): string
  {
    return 'broken-link-editor-highlight';
  }

  public function getDependencies(): array
  {
    return ['editor', 'jquery'];
  }

  public function getFilename(): string
  {
    return 'js/editor-highlight.js';
  }

  public function getLocalizeData(): ?array
  {
    if (!$this->registry || !$this->getPostid()) {
      return [];
    }

    $links = $this->registry->getBrokenLinksByPostId(
      $this->getPostid()
    );
    $links = array_column($links, 'url');

    return [
      'links' => $links,
    ];
  }

  /**
   * Get the post id
   */
  private function getPostid(): ?int
  {
    global $post;
    return $post->ID ?? null;
  }
}