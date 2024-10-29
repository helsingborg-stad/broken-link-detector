<?php 

namespace BrokenLinkDetector\LinkList;

use BrokenLinkDetector\Link\Link;

interface LinkListInterface
{
    public function addLink(Link $link): void;
    public function getLinks(): array;
    public function getLinkCount(): int;
}