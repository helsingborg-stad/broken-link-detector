<?php

namespace BrokenLinkDetector\BrokenLinkRegistry\FindLink;

use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkInterface;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;
use BrokenLinkDetector\BrokenLinkRegistry\LinkList\LinkList;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;

class FindLinkFromPostContent implements FindLinkInterface
{
  private $linkList = null;

  public function __construct(private $wpService, private Config $config, private Database $db)
  {
    if(is_null($this->linkList)) {
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
   * Find all links in the given post content.
   *
   * @param string $postContent
   */
  public function findLinks(): LinkList
  {
    $query = $this->createQuery();

    $postsContainingLinks = $this->db->getInstance()->get_results($query);

    if(!$this->wpService->isWpError($postsContainingLinks)) {
      foreach ($postsContainingLinks as $postItem) {

        $extractedLinks = $this->extractLinksFromPostContent($postItem->post_content);

        foreach ($extractedLinks as $url) {
          $this->linkList->addLink(
            Link::createLink(
              $url, 
              null, 
              $postItem->ID, 
              $this->wpService, 
              $this->config
            )
          );
        }
      }
    }
    

    var_dump($this->linkList);

    return $this->linkList;
  }

  /**
   * Extract all links from the given post content.
   *
   * @param string $postContent
   * @return array
   */
  private function extractLinksFromPostContent(string $postContent): array
  {
    $matches = [];
    preg_match_all('/href="([^"]+)"/', $postContent, $matches);

    return $matches[1];
  }


  public function createQuery() {

    //Init DB object
    $db = $this->db->getInstance();

    // Get configuration
    $bannedPostTypesArray   = $this->config->linkDetectBannedPostTypes();
    $allowedPostStatuses    = $this->config->linkDetectAllowedPostStatuses();

    // Prepare placeholders for each banned post type and allowed status
    $placeholdersTypes      = implode(',', array_fill(0, count($bannedPostTypesArray), '%s'));
    $placeholdersStatuses   = implode(',', array_fill(0, count($allowedPostStatuses), '%s'));

    // Prepare the SQL statement
    $query = $db->prepare('
            SELECT ID, post_content
            FROM ' . $db->posts . '
            WHERE
                post_content RLIKE ("href=\\".*?\\"")
                AND post_type NOT IN (' . $placeholdersTypes . ')
                AND post_status NOT IN (' . $placeholdersStatuses . ')
        ', array_merge(
            $bannedPostTypesArray,
            $allowedPostStatuses
        )
    );

    return $query;
  }

}