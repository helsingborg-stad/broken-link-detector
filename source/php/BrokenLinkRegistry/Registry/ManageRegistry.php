<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry\Registry;

use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Config\Config;

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
  public function add(array $data, int $postId): void
  {
    $uniqueRecordHash = $this->hash($data['url'], $data['post_id']);
    if(!empty($data) && is_array($data)) {
      foreach ($data as $item) {
        $this->db->getInstance()->insert(
          $this->config->getTableName(), 
          array(
              'post_id' => $item['post_id'],
              'url' => $item['url'],
              'unique_hash' => $uniqueRecordHash,
          ), 
          array('%d', '%s')
        );
      }
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