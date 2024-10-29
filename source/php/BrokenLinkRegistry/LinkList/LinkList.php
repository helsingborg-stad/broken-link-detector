<?php 

namespace BrokenLinkDetector\LinkList;

use BrokenLinkDetector\Link\Link;

class LinkList implements LinkListInterface
{
    private $links = [];

    public function addLink(Link $link): void
    {
      $this->links[] = $link;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function getLinkCount(): int
    {
        return count($this->links);
    }

}