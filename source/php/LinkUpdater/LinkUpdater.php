<?php 


namespace BrokenLinkDetector;

use WpService\WpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\HooksRegistrar\Hookable;

class LinkUpdater implements Hookable
{
    public function __construct(private WpService $wpService, private Config $config, private Database $database)
    {
    }

    public function addHooks(): void
    {
      $this->wpService->addAction('wp_insert_post_data', array($this, 'updateLinks'), 10, 2);
    }

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
    private function createPermalink(int $postId, $postName): string
    {

      // TODO: This is not right! 
      return $this->wpService->getPermalink($postId) . $postName;
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