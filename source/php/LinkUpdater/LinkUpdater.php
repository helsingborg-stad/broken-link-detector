<?php 


namespace BrokenLinkDetector\LinkUpdater;

use WpService\WpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\HooksRegistrar\Hookable;

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
      $this->wpService->addAction('wp_insert_post_data', array($this, 'updateLinks'), 10, 2);
    }

    /**
     * Update the links in the post content if the post name has changed
     * @param array $data
     * @param array $post
     * @return bool
     */
    public function updateLinks(array $data, array $post): bool
    {
      if($this->linkHasChanged($data, $post) && !$this->shouldReplaceForPosttype($data['post_type'])) {
       
        $postId = $post['ID'] ?? null;

        if(is_numeric($postId)) {
          $this->replaceLinks(
            $this->createPermalink($postId, $data['post_name']), 
            $this->createPermalink($postId, $post['post_name'])
          );
          return true;
        }
      }
      return false;
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
     * @param array $data   The newly submitted data
     * @param array $post   The stored post data
     * @return bool
     */
    private function linkHasChanged(array $data, array $post): bool
    {
      return $data['post_name'] !== $post['post_name'];
    }

    /**
     * Check if the link should be replaced for the post type
     * @param string $postType
     * @return bool
     */
    private function shouldReplaceForPosttype(string $postType): bool
    {
      return !in_array($postType, $this->config->getDisabledLinkReplacementPostTypes());
    }
}