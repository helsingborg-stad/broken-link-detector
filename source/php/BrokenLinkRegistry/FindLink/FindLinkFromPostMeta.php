<?php

declare(strict_types=1);

namespace BrokenLinkDetector\BrokenLinkRegistry\FindLink;

use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkInterface;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;
use BrokenLinkDetector\BrokenLinkRegistry\LinkList\LinkList;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;

class FindLinkFromPostMeta implements FindLinkInterface
{
    private $linkList = null;

    public function __construct(
        private $wpService,
        private Config $config,
        private Database $db,
    ) {
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
    public function findLinks(?int $postId = null): LinkList
    {
        $query = $this->createQuery($postId);
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
                            $this->config,
                        ),
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
    public function createQuery(?int $postId = null): string
    {
        // Init DB object
        $db = $this->db->getInstance();

        // Get configuration
        $bannedPostTypesArray = $this->config->linkDetectBannedPostTypes();
        $allowedPostStatuses = $this->config->linkDetectAllowedPostStatuses();

        // Prepare placeholders for each banned post type and allowed status
        $placeholdersTypes = implode(',', array_fill(0, count($bannedPostTypesArray), '%s'));
        $placeholdersStatuses = implode(',', array_fill(0, count($allowedPostStatuses), '%s'));

        // Start building the base SQL query
        $query = '
            SELECT post_id, meta_value
            FROM ' . $db->postmeta . ' pm
            INNER JOIN ' . $db->posts . ' p ON pm.post_id = p.ID
            WHERE
                meta_value RLIKE "(https?:\\/\\/[^\\s\"]+)"
                AND p.post_type NOT IN (' . $placeholdersTypes . ')
                AND p.post_status NOT IN (' . $placeholdersStatuses . ')';

        // If post_id is provided, add the condition to the query
        if ($postId !== null) {
            $query .= ' AND pm.post_id = %d';
        }

        // Merge parameters for binding (banned post types, allowed post statuses, and optional post_id)
        $queryParams = array_merge($bannedPostTypesArray, $allowedPostStatuses);
        if ($postId !== null) {
            $queryParams[] = $postId; // Add post_id to parameters if provided
        }

        // Prepare the SQL statement
        return $db->prepare($query, $queryParams);
    }
}
