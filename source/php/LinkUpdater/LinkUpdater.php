<?php

namespace BrokenLinkDetector\LinkUpdater;

use WpService\WpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use WP_Post;

class LinkUpdater implements LinkUpdaterInterface, Hookable
{
  private ?string $storedPermalink = null;

  public function __construct(private WpService $wpService, private Config $config, private Database $database)
  {
  }

  /**
   * Add hooks for the link updater
   * @return void
   */
  public function addHooks(): void
  {
    //Fetches the previous permalink before the post is updated
    $this->wpService->addAction('pre_post_update', [$this, 'beforeUpdateLinks'], 10, 2);

    //Updates the links in the post content if the post name has changed
    $this->wpService->addAction('post_updated', [$this, 'updateLinks'], 10, 3);
  }

  /**
   * Before the post is updated, store the post data
   * @param int $postId
   * @param array $post
   * @return void
   */
  public function beforeUpdateLinks(int $postId, array $post): void
  {
    $this->storedPermalink = $this->wpService->getPermalink($postId);
  }

  /**
   * Update the links in the post content if the post name has changed
   * @param int $id
   * @param WP_Post $postBefore
   * @param WP_Post $postAfter
   * @return void
   */
  public function updateLinks(int $postId, WP_Post $postBefore, WP_Post $postAfter): void
  {
    $previousPermalink  = $this->storedPermalink;
    $currentPermalink   = $this->wpService->getPermalink($postId);

    if (!$this->isValidReplaceRequest($previousPermalink, $currentPermalink)) {
      return;
    }

    if ($this->shouldReplaceForPosttype($postAfter->post_type)) {
      $this->replaceLinks($previousPermalink, $currentPermalink);
    }
  }

  /**
   * Validate the replace request
   * @param string $oldLink
   * @param string $newLink
   * @return bool
   */
  private function isValidReplaceRequest(string $oldLink, string $newLink): bool
  {
    //Ensure that the old link is not the same as the new link
    if ($oldLink === $newLink) {
      return false; 
    }

    //Ensure that the old link is not empty
    if (empty($oldLink)) {
      return false;
    }

    //Ensure that the new link is not empty
    if (empty($newLink)) {
      return false;
    }

    //Ensure that the old link is not a home url
    if ($this->wpService->homeUrl() === $oldLink) {
      return false;
    }

    return true;
  }

  /**
   * Replace the old link with the new link in the post content
   * @param string $oldLink
   * @param string $newLink
   * @return int
   */
  private function replaceLinks(string $oldLink, string $newLink): int
  {
      $db = $this->database->getInstance();

      // Get allowed post statuses and banned post types from config
      $allowedPostStatuses = $this->config->linkDetectAllowedPostStatuses();
      $bannedPostTypes = $this->config->linkUpdaterBannedPostTypes();

      // Prepare placeholders for post statuses and post types
      $statusPlaceholders = implode(',', array_fill(0, count($allowedPostStatuses), '%s'));
      $typePlaceholders = implode(',', array_fill(0, count($bannedPostTypes), '%s'));

      // Build the query with post status and post type filters
      $queryParams = ['%' . $db->esc_like($oldLink) . '%'];
      $queryParams = array_merge($queryParams, $allowedPostStatuses);
      $queryParams = array_merge($queryParams, $bannedPostTypes);

      //Get the post ids that contain the old link with proper filtering
      $postIds = $db->get_col(
          $db->prepare(
              "SELECT ID
                  FROM $db->posts
                  WHERE post_content LIKE %s
                  AND post_status IN ($statusPlaceholders)
                  AND post_type NOT IN ($typePlaceholders)",
              $queryParams
          )
      );

      //Update the post content with the same filtering
      $updateParams = [$oldLink, $newLink];
      $updateParams = array_merge($updateParams, ['%' . $db->esc_like($oldLink) . '%']);
      $updateParams = array_merge($updateParams, $allowedPostStatuses);
      $updateParams = array_merge($updateParams, $bannedPostTypes);

      $db->query(
          $db->prepare(
              "UPDATE $db->posts
                  SET post_content = REPLACE(post_content, %s, %s)
                  WHERE post_content LIKE %s
                  AND post_status IN ($statusPlaceholders)
                  AND post_type NOT IN ($typePlaceholders)",
              $updateParams
          )
      );

      //Clear the object cache for the post ids
      $this->clearObjectCache($postIds);

      //Return the number of rows affected
      return $db->rows_affected;
  }

  /**
   * Clear the object cache for the post ids
   * 
   * @param array $postIds
   */
  private function clearObjectCache(array $postIds): void
  {
    foreach ($postIds as $postId) {
      $this->wpService->cleanPostCache($postId);
    }
  }

  /**
   * Check if the post type should be replaced
   * @param string $postType
   * @return bool
   */
  private function shouldReplaceForPosttype(string $postType): bool
  {
    return !in_array($postType, $this->config->linkUpdaterBannedPostTypes());
  }
}