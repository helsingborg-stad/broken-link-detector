<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry\LinkList;

use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;

interface LinkListInterface
{
    public function addLink(Link $link): void;
    public function classifyLinks(): bool;
    public function getLinks(): array;
    public function getLinkCount(): int;
}