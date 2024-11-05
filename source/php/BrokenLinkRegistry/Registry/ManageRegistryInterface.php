<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry\Registry;

use BrokenLinkDetector\BrokenLinkRegistry\LinkList\LinkList;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;

interface ManageRegistryInterface
{
  public function add(LinkList|Link $data): bool;
  public function remove(int $postId): void;
  public function update(LinkList|Link $data): bool;
  public function getLinksThatNeedsClassification(int $maxLimit): array;
}