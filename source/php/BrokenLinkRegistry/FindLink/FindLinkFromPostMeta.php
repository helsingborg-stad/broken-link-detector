<?php 

namespace BrokenLinkDetector\FindLink;

class FindLinkFromMeta implements FindLinkInterface
{
    public function findLink(array $data): string
    {
        return $data['link'];
    }
}