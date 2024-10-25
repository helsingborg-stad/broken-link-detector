<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry;

interface BrokenLinkRegistryInterface
{
  public function add(array $data, int $postId): void;
}