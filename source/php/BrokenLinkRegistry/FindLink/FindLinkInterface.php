<?php

declare(strict_types=1);

namespace BrokenLinkDetector\BrokenLinkRegistry\FindLink;

use BrokenLinkDetector\BrokenLinkRegistry\LinkList\LinkList;

interface FindLinkInterface
{
    public function findLinks(): LinkList;

    public function getHookName(): string;

    public function getHookPriority(): int;

    public function getHookAcceptedArgs(): int;
}
