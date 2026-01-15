<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Hooks;

use BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\WpService;

class MaintainLinkRegistryOnSavePost implements Hookable
{
    public function __construct(
        private WpService $wpService,
        private Config $config,
        private Database $db,
        private ManageRegistry $registry,
    ) {}

    public function addHooks(): void
    {
        $this->wpService->addAction('save_post', [$this, 'clearLinksOnSavePost'], 10, 3);
        $this->wpService->addAction('save_post', [$this, 'findLinksOnSavePost'], 20, 3);
    }

    /**
     * The callback for the 'save_post' action. It will trigger the link clearing logic
     * whenever a post is saved.
     *
     * @param int $postId The post ID.
     * @param WP_Post $post The post object.
     * @param bool $update Whether this is an update or a new post.
     */
    public function clearLinksOnSavePost(int $postId, \WP_Post $post, bool $update): void
    {
        // Do not run on autosave or revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $this->registry->remove($postId);
    }

    /**
     * The callback for the 'save_post' action. It will trigger the link finding logic
     * whenever a post is saved.
     *
     * @param int $postId The post ID.
     * @param WP_Post $post The post object.
     * @param bool $update Whether this is an update or a new post.
     */
    public function findLinksOnSavePost(int $postId, \WP_Post $post, bool $update): void
    {
        // Do not run on autosave or revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Collect links from post content and metadata
        $this->findLinksInContent($postId);
        $this->findLinksInMeta($postId);
    }

    /**
     * Find links in the content of the post and register them.
     *
     * @param int $postId The post ID.
     */
    private function findLinksInContent(int $postId): void
    {
        $findLinkFromPostContent = new \BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostContent(
            $this->wpService,
            $this->config,
            $this->db,
        );
        $foundLinks = $findLinkFromPostContent->findLinks($postId);
        if ($foundLinks->getLinkCount() !== 0) {
            $this->registry->add($foundLinks);
        }
    }

    /**
     * Find links in the post meta and register them.
     *
     * @param int $postId The post ID.
     */
    private function findLinksInMeta(int $postId): void
    {
        $findLinkFromPostMeta = new \BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostMeta(
            $this->wpService,
            $this->config,
            $this->db,
        );
        $foundLinks = $findLinkFromPostMeta->findLinks($postId);
        if ($foundLinks->getLinkCount() !== 0) {
            $this->registry->add($foundLinks);
        }
    }
}
