<?php

declare(strict_types=1);

namespace BrokenLinkDetector\BrokenLinkRegistry\Registry;

use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;
use BrokenLinkDetector\BrokenLinkRegistry\LinkList\LinkList;

interface ManageRegistryInterface
{
    public function add(LinkList|Link $data): bool;

    public function remove(int $postId): void;

    public function update(LinkList|Link $data): bool;

    public function getLinksThatNeedsClassification(int $maxLimit): array;
}
