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
   * @return void
   */
  public function add(LinkList|Link $data): void
  {
    if ($data instanceof LinkList) {
      $this->addLinkList($data);
    }
    if ($data instanceof Link) {
      $this->addLink($data);
    }
  }

  private function addLink(Link $link): void
  {

    echo "ADD...";

    /*$this->db->getInstance()->insert(
      $this->config->getTableName(),
      array(

      ),
      array('%d', '%s', '%s')
    );*/ 
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