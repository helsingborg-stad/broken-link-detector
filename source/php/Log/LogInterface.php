<?php 

namespace BrokenLinkDetector\Log;

interface LogInterface
{
  public function add(array $data, int $postId): void;
}