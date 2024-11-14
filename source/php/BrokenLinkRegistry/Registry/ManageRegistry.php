<?php 

namespace BrokenLinkDetector\BrokenLinkRegistry\Registry;

use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;
use BrokenLinkDetector\BrokenLinkRegistry\LinkList\LinkList; 
use BrokenLinkDetector\Cli\Log;

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

  /**
   * Update broken links (if already in db, checked by hash)
   * @param  array $data
   * @return bool
   */
  public function update(LinkList|Link $linkListOrLink): bool
  {
    if ($linkListOrLink instanceof LinkList) {
      $this->updateLinkList($linkListOrLink);
      return true;
    }
    if ($linkListOrLink instanceof Link) {
      $this->updateLink($linkListOrLink);
      return true;
    }
    return false;
  }

  /**
   * Update a link in the registry
   * 
   * @param Link $link
   * 
   * @return void
   */
  private function updateLink(Link $link): void
  {
      $httpCode   = $link->classification?->getHttpCode() ?? null;

      $x = $this->db->getInstance()->update(
        $this->db->getInstance()->prefix . $this->config->getTableName(),
          array(
              'http_code'  => $httpCode,
              'time'       => current_time('mysql')  // Set the current timestamp
          ),
          array(
              'url' => $this->normalizeUrl($link->url),
          ),
          array('%d', '%s'),
          array('%s')
      ); 

      //Log results
      if ($lastError = $this->db->getInstance()->last_error) {
        Log::warning("Error updating link in registry: " . $lastError);
      }
  }

  /**
   * Update a list of links in the registry
   * 
   * @param LinkList $linkList
   * 
   * @return void
   */
  private function updateLinkList(LinkList $linkList): void
  {
    foreach ($linkList->getLinks() as $link) {
      $this->updateLink($link);
    }
  }

  /**
   * Add a link to the registry
   * 
   * @param Link $link
   * 
   * @return void
   */
  private function addLink(Link $link): void
  {
      $uniqueHash = $this->hash($link->url, $link->postId);
      $httpCode   = $link->classification?->getHttpCode() ?? null;

      $this->db->getInstance()->insert(
        $this->db->getInstance()->prefix . $this->config->getTableName(),
          array(
              'post_id'    => $link->postId,
              'url'        => $this->normalizeUrl($link->url),
              'unique_hash' => $uniqueHash,
              'http_code'  => $httpCode,
              'time'       => current_time('mysql')  // Set the current timestamp
          ),
          array('%d', '%s', '%s', '%d', '%s')
      ); 

      //Log results
      if ($lastError = $this->db->getInstance()->last_error) {
          Log::warning("Error adding link to registry: " . $lastError . " for link: " . $link->url . " with postID: " . $link->postId);
      }
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
   * Get all unclassified links
   * @return array
   */
  public function getLinksThatNeedsClassification($maxLimit = null, $random = true): array
  {
      $recheckInterval = $this->config->getRecheckInterval();

      $results = $this->db->getInstance()->get_results(
          $this->db->getInstance()->prepare("
              SELECT * FROM 
              " . $this->db->getInstance()->prefix . $this->config->getTableName() . " 
              WHERE http_code IS NULL 
              OR time < DATE_SUB(NOW(), INTERVAL %d MINUTE)
              ORDER BY " . ($random ? "RAND()" : "time ASC") . "
              LIMIT %d", 
            $recheckInterval, (is_int($maxLimit) ? $maxLimit : PHP_INT_MAX)
          )
      );

      return $results;
  }

  /**
   * Get broken links by post id
   * @param  integer $postId
   * @return array
   */
  public function getBrokenLinksByPostId(int $postId): array
  {
    $httpCodesConsideredBroken = $this->config->responseCodesConsideredBroken();
    return $this->db->getInstance()->get_results(
      $this->db->getInstance()->prepare(
        "SELECT * FROM " . $this->db->getInstance()->prefix . $this->config->getTableName() . " 
        WHERE post_id = %d 
        AND http_code IN (" . implode(',', $httpCodesConsideredBroken) . ")",
        $postId
      )
    );
  }

  /**
   * Create a hash from a normalized url and post id.
   *
   * @param string $url
   * @param integer $postId
   * @return string
   */
  public function hash($url, $postId): string
  {
    return md5($this->normalizeUrl($url) . (string) $postId);
  }

  /**
   * Normalize the URL to remove insignificant differences.
   * 
   * @param string $url
   * 
   * @return string     The normalized URL
   */
  private function normalizeUrl($url): string
  {
    $parsedUrl = parse_url($url);

    $scheme = isset($parsedUrl['scheme']) ? strtolower($parsedUrl['scheme']) : 'http';
    $host = isset($parsedUrl['host']) ? strtolower($parsedUrl['host']) : '';
    $path = isset($parsedUrl['path']) ? rtrim($parsedUrl['path'], '/') : '';
    $query = '';

    if (isset($parsedUrl['query'])) {
      parse_str($parsedUrl['query'], $queryParams);
      ksort($queryParams);
      $query = http_build_query($queryParams);
    }

    $normalizedUrl = $scheme . '://' . $host . $path;
    if ($query) {
      $normalizedUrl .= '?' . $query;
    }

    return $normalizedUrl;
  }
}