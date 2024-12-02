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
    $this->wpService->addFilter('pre_post_update', [$this, 'beforeUpdateLinks'], 10, 2);

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
    if ($this->storedPermalink === null) {
      return;
    }

    $previousPermalink  = $this->storedPermalink;
    $currentPermalink   = $this->wpService->getPermalink($postId);

    if ($previousPermalink != $currentPermalink && $this->shouldReplaceForPosttype($postAfter->post_type)) {
      //Ensure that the home url is not replaced
      if($this->wpService->homeUrl() !== $previousPermalink) {
        $this->replaceLinks($previousPermalink, $currentPermalink);
      }
    }
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
      $db->query(
          $db->prepare(
              "UPDATE $db->posts
                  SET post_content = REPLACE(post_content, %s, %s)
                  WHERE post_content LIKE %s",
              $oldLink,
              $newLink,
              '%' . $db->esc_like($oldLink) . '%'
          )
      );
      return $db->rows_affected;
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