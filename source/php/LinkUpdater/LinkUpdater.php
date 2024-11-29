<?php 


namespace BrokenLinkDetector\LinkUpdater;

use WpService\WpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use WP_Post;

class LinkUpdater implements LinkUpdaterInterface, Hookable
{
    public function __construct(private WpService $wpService, private Config $config, private Database $database)
    {
    }

    /**
     * Add hooks for the link updater
     * @return void
     */
    public function addHooks(): void
    {
      $this->wpService->addAction('wp_after_insert_post', array($this, 'updateLinks'), 10, 3);
    }

    /**
     * Update the links in the post content if the post name has changed
     * @param array $data
     * @param array $post
     * @return bool
     */
    public function updateLinks(int|WP_Post $post, bool $isUpdate, null|WP_Post $postBefore): void
    {
      if(!$isUpdate) {
        return;
      }

      if(!is_a($post, 'WP_Post') && is_numeric($post)) {
        $post = $this->wpService->getPost($post);
      }

      if($this->linkHasChanged($post, $postBefore) && $this->shouldReplaceForPosttype($this->wpService->getPostType($post))) {
        $this->replaceLinks(
          $this->createPermalink($post->ID, $postBefore->post_name), 
          $this->createPermalink($post->ID, $post->post_name)
        );
      }
    }

    /**
     * Replace the old link with the new link in posts that contains the link
     * @param string $newLink
     * @param string $oldLink
     * @return int
     */
    private function replaceLinks(string $newLink, string $oldLink): int
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
     * Create a permalink from the post id and post name
     * @param int $postId
     * @param string $postName
     * @return string
     */
    public function createPermalink(int $postId, string $postName): string
    {
        $permalink =  preg_replace('/[^\/]+\/?$/',
          $postName, 
          $this->wpService->getPermalink($postId)
        );

        $permalink = rtrim($permalink, '/');

        return $permalink; 
    }

    /**
     * Check if the link has changed
     * @param WP_Post $post   The newly submitted data
     * @param WP_Post $postBefore   The stored post data
     * @return bool
     */
    public function linkHasChanged(WP_Post $post, WP_Post $postBefore): bool
    {
      return $post->post_name !== $postBefore->post_name;
    }

    /**
     * Check if the link should be replaced for the post type
     * @param string $postType
     * @return bool
     */
    private function shouldReplaceForPosttype(string $postType): bool
    {
      return !in_array($postType, $this->config->linkUpdaterBannedPostTypes());
    }
}