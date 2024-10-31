<?php

namespace BrokenLinkDetector\BrokenLinkRegistry\FindLink;

use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkInterface;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;
use BrokenLinkDetector\BrokenLinkRegistry\LinkList\LinkList;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;

class FindLinkFromPostMeta implements FindLinkInterface
{
    private $linkList = null;

    public function __construct(private $wpService, private Config $config, private Database $db)
    {
        if (is_null($this->linkList)) {
            $this->linkList = new LinkList();
        }
    }

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

    /**
     * Find all links in the given post meta content.
     *
     * @return LinkList
     */
    public function findLinks(): LinkList
    {
        $query = $this->createQuery();
        $metaContainingLinks = $this->db->getInstance()->get_results($query);

        if (!$this->wpService->isWpError($metaContainingLinks)) {
            foreach ($metaContainingLinks as $metaItem) {
                $extractedLinks = $this->extractLinksFromMetaContent($metaItem->meta_value);

                foreach ($extractedLinks as $url) {
                    $this->linkList->addLink(
                        Link::createLink(
                            $url,
                            null,
                            $metaItem->post_id,
                            $this->wpService,
                            $this->config
                        )
                    );
                }
            }
        }

        return $this->linkList;
    }

    /**
     * Extract all links from the given post meta content.
     *
     * @param string $metaContent
     * @return array
     */
    private function extractLinksFromMetaContent(string $metaContent): array
    {
        $matches = [];
        preg_match_all('/(https?:\/\/[^"\s]+)/', $metaContent, $matches);
        return $matches[0];
    }

    /**
     * Create the SQL query to find all post meta entries containing links.
     *
     * @return string
     */
    public function createQuery()
    {
        // Init DB object
        $db = $this->db->getInstance();

        // Get configuration
        $bannedPostTypesArray = $this->config->linkDetectBannedPostTypes();
        $allowedPostStatuses  = $this->config->linkDetectAllowedPostStatuses();

        // Prepare placeholders for each banned post type and allowed status
        $placeholdersTypes    = implode(',', array_fill(0, count($bannedPostTypesArray), '%s'));
        $placeholdersStatuses = implode(',', array_fill(0, count($allowedPostStatuses), '%s'));

        // Prepare the SQL statement
        $query = $db->prepare('
            SELECT post_id, meta_value
            FROM ' . $db->postmeta . ' pm
            INNER JOIN ' . $db->posts . ' p ON pm.post_id = p.ID
            WHERE
                meta_value RLIKE ("(https?:\\/\\/[^\\s\"]+)")
                AND p.post_type NOT IN (' . $placeholdersTypes . ')
                AND p.post_status NOT IN (' . $placeholdersStatuses . ')
        ', array_merge(
            $bannedPostTypesArray,
            $allowedPostStatuses
        ));

        return $query;
    }
}