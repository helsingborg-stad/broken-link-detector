<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry\FindLink;

use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkInterface;
class FindLinkFromMeta implements FindLinkInterface
{

    public function getHookName(): string
    {
        return __NAMESPACE__ . __CLASS__;
    }

    public function getHookPriority(): int
    {
        return 10;
    }

    public function getHookAcceptedArgs(): int
    {
        return 1;
    }

    public function findLink(array $data): string
    {
        return $data['link'];
    }
}