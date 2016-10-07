<?php

namespace BrokenLinkDetector;

class InternalDetector
{
    public $permalinkBefore;
    public $permalinkAfter;

    public $permalinksUpdated = 0;

    public function __construct($data, $postarr)
    {
        $this->getPermalinkBefore($postarr['ID']);

        add_action('save_post', array($this, 'getPermalinkAfter'));
    }

    /**
     * Get permalink before save
     * @param  integer $postId Post id
     * @return void
     */
    public function getPermalinkBefore($postId)
    {
        if (wp_is_post_revision($postId)) {
            return;
        }

        $this->permalinkBefore = get_permalink($postId);
    }

    /**
     * Get new permalink (after save)
     * @param  integer $postId Post id
     * @return void
     */
    public function getPermalinkAfter($postId)
    {
        $this->permalinkAfter = get_permalink($postId);
        remove_action('save_post', array($this, 'getPermalinkAfter'));

        if ($this->permalinkBefore && !empty($this->permalinkBefore)) {
            $this->detectChangedPermalink();
        }
    }

    public function detectChangedPermalink()
    {
        // if permalink not changed, return, do nothing more
        if ($this->permalinkBefore === $this->permalinkAfter) {
            return false;
        }

        // Replace occurances of the old permalink with the new permalink
        global $wpdb;
        $query = $wpdb->prepare(
            "UPDATE $wpdb->posts
                SET post_content = REPLACE(post_content, %s, %s)
                WHERE post_content LIKE %s",
            $this->permalinkBefore,
            $this->permalinkAfter,
            '%' . $wpdb->esc_like($this->permalinkBefore) . '%'
        );

        $wpdb->query($query);
        $this->permalinksUpdated += $wpdb->rows_affected;

        add_notice(sprintf('%d links to this post was updated to use the new permalink.', $this->permalinksUpdated));

        return true;
    }
}
