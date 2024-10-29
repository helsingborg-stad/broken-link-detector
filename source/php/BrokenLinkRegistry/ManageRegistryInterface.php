<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry;

interface ManageRegistryInterface
{
  public function add(array $data, int $postId): void;
  public function remove(int $postId): void;
}