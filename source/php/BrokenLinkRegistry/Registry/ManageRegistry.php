<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry\Registry;

use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;
use BrokenLinkDetector\BrokenLinkRegistry\LinkList\LinkList; 

class ManageRegistry implements ManageRegistryInterface
{
  public function __construct(private Database $db, private Config $config)
  {
  }

  /**
   * Save broken links (if not already in db, checked by hash)
   * @param  array $data
   * @return bool
   */
  public function add(LinkList|Link $linkListOrLink): bool
  {
    if ($linkListOrLink instanceof LinkList) {
      $this->addLinkList($linkListOrLink);
      return true;
    }
    if ($linkListOrLink instanceof Link) {
      $this->addLink($linkListOrLink);
      return true;
    }
    return false;
  }

  private function addLink(Link $link): void
  {
      $uniqueHash = $this->hash($link->url, $link->postId);
      $httpCode   = $link->classification->getHttpCode() ?? null;

      $this->db->getInstance()->insert(
          $this->config->getTableName(),
          array(
              'post_id'    => $link->postId,
              'url'        => $link->url,
              'unique_hash' => $uniqueHash,
              'http_code'  => $httpCode,
              'time'       => current_time('mysql')  // Set the current timestamp
          ),
          array('%d', '%s', '%s', '%d', '%s')
      ); 
  }

  /**
   * Add a list of links to the registry
   * 
   * @param LinkList $linkList
   * 
   * @return void
   */
  private function addLinkList(LinkList $linkList): void
  {
    foreach ($linkList->getLinks() as $link) {
      $this->addLink($link);
    }
  }

  /**
   * Delete broken links by post id (eg. when a post is removed)
   * @param  integer $postId
   * @return void
   */
  public function remove(int $postId): void
  {
    $this->db->getInstance()->delete(
      $this->config->getTableName(),
      array('post_id' => $postId),
      array('%d')
    );
  }

  /**
   * Hash the url and post id
   * @param  string $url
   * @param  integer $postId
   * @return string
   */
  private function hash($url, $postId): string
  {
      return md5($url . $postId);
  }
}