<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry\LinkList;

use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;

class LinkList implements LinkListInterface
{
    private $links = [];

    /**
     * Add a link to the list.
     * 
     * @param Link $link
     * 
     * @return void
     */
    public function addLink(Link $link): void
    {
      $this->links[] = $link;
    }

    /**
     * Classify all links in the list.
     * 
     * @return bool True if all links were classified, false if no links avabile to classify.
     */
    public function classifyLinks(): bool
    {
        if (empty($this->links)) {
            return false;
        }
        foreach ($this->links as &$link) {
            $link->classify();
        }
        return true;
    }

    /**
     * Get all links in the list.
     * 
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Get the number of links in the list.
     * 
     * @return int
     */
    public function getLinkCount(): int
    {
        return count($this->links);
    }

}