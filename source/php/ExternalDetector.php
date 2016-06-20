<?php

namespace BrokenLinkDetector;

class ExternalDetector
{
    public function __construct()
    {
        add_action('init', array($this, 'schedule'));
        add_action('broken-links-detector-external', array($this, 'lookForBrokenLinks'));
    }

    public function schedule()
    {
        if (wp_next_scheduled('broken-links-detector-external')) {
            return;
        }

        wp_schedule_event(time(), 'daily', 'broken-links-detector-external');
    }

    public function lookForBrokenLinks()
    {
        $foundUrls = array();

        global $wpdb;
        $posts = $wpdb->get_results("SELECT ID, post_content FROM wp_posts WHERE post_content RLIKE ('href=*') LIMIT 10");

        foreach ($posts as $post) {
            preg_match_all('/<a[^>]+href=([\'"])(http|https)(.+?)\1[^>]*>/i', $post->post_content, $m);

            if (!isset($m[3]) || count($m[3]) > 0) {
                foreach ($m[3] as $key => $url) {
                    $url = $m[2][$key] . $url;

                    if (!$this->isBroken($url)) {
                        continue;
                    }

                    $foundUrls[] = array(
                        'post_id' => $post->ID,
                        'url' => $url
                    );
                }
            }
        }

        $this->saveBrokenLinks($foundUrls);
    }

    /**
     * Save broken links (if not already in db)
     * @param  array $data
     * @return void
     */
    public function saveBrokenLinks($data)
    {
        global $wpdb;

        $inserted = array();
        $tableName = \BrokenLinkDetector\App::$dbTable;

        foreach ($data as $item) {
            $exists = $wpdb->get_row("SELECT id FROM $tableName WHERE post_id = {$item['post_id']} AND url = '{$item['url']}'");
            if ($exists) {
                continue;
            }

            $inserted[] = $wpdb->insert(\BrokenLinkDetector\App::$dbTable, array(
                'post_id' => $item['post_id'],
                'url' => $item['url']
            ), array('%d', '%s'));
        }

        return true;
    }

    public function isBroken($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return true;
        }

        $headers = @get_headers($url);

        if (strtolower($headers[0]) == 'http/1.1 404 not found') {
            return true;
        }

        return false;
    }
}
