<?php

namespace BrokenLinkDetector;

class ExternalDetector
{
    public function __construct()
    {
        add_action('wp', array($this, 'schedule'));
        add_action('broken-links-detector-external', array($this, 'lookForBrokenLinks'));

        add_action('save_post', function ($postId) {
            if (wp_is_post_revision($postId) || !isset($_POST['broken-link-detector-rescan']) || $_POST['broken-link-detector-rescan'] !== 'true') {
                return;
            }

            $this->lookForBrokenLinks($postId);
        });

        if (isset($_GET['broken-links-detector']) && $_GET['broken-links-detector'] == 'scan') {
            do_action('broken-links-detector-external');
        }
    }

    public function schedule()
    {
        if (wp_next_scheduled('broken-links-detector-external')) {
            return;
        }

        wp_schedule_event(time(), 'daily', 'broken-links-detector-external');
    }

    /**
     * Look for broken links in post_content
     * @param integer $post_id Optional post_id to update broken links for
     * @return void
     */
    public function lookForBrokenLinks($postId = null, $url = null)
    {
        \BrokenLinkDetector\App::checkInstall();
        $foundUrls = array();

        if ($url) {
            $url = "REGEXP ('.*(href=\"{$url}\").*')";
        } else {
            $url = "RLIKE ('href=*')";
        }

        global $wpdb;
        $sql = "
            SELECT ID, post_content
            FROM $wpdb->posts
            WHERE
                post_content {$url}
                AND post_type NOT IN ('attachment', 'revision', 'acf', 'acf-field', 'acf-field-group')
                AND post_status IN ('publish', 'private', 'password')
        ";

        if (is_numeric($postId)) {
            $sql .= " AND ID = $postId";
        }

        $posts = $wpdb->get_results($sql);

        foreach ($posts as $post) {
            preg_match_all('/<a[^>]+href=([\'"])(http|https)(.+?)\1[^>]*>/i', $post->post_content, $m);

            if (!isset($m[3]) || count($m[3]) > 0) {
                foreach ($m[3] as $key => $url) {
                    $url = $m[2][$key] . $url;

                    // Replace whitespaces in url
                    if (preg_match('/\s/', $url)) {
                        $newUrl = preg_replace('/ /', '%20', $url);
                        $wpdb->query(
                            $wpdb->prepare(
                                "UPDATE $wpdb->posts
                                    SET post_content = REPLACE(post_content, %s, %s)
                                    WHERE post_content LIKE %s
                                    AND ID = %d",
                                $url,
                                $newUrl,
                                '%' . $wpdb->esc_like($url) . '%',
                                $post->ID
                            )
                        );
                        $url = $newUrl;
                    }

                    if ($postId !== 'internal' && !$this->isBroken($url)) {
                        continue;
                    }

                    $foundUrls[] = array(
                        'post_id' => $post->ID,
                        'url' => $url
                    );
                }
            }
        }

        $this->saveBrokenLinks($foundUrls, $postId);
    }

    /**
     * Save broken links (if not already in db)
     * @param  array $data
     * @return void
     */
    public function saveBrokenLinks($data, $postId = null)
    {
        global $wpdb;

        $inserted = array();
        $tableName = \BrokenLinkDetector\App::$dbTable;

        if (is_numeric($postId)) {
            $wpdb->delete($tableName, array('post_id' => $postId), array('%d'));
        } elseif (is_null($postId)) {
            $wpdb->query("TRUNCATE $tableName");
        }

        foreach ($data as $item) {
            $exists = $wpdb->get_row("SELECT id FROM $tableName WHERE post_id = {$item['post_id']} AND url = '{$item['url']}'");

            if ($exists) {
                continue;
            }

            $inserted[] = $wpdb->insert($tableName, array(
                'post_id' => $item['post_id'],
                'url' => $item['url']
            ), array('%d', '%s'));
        }

        return true;
    }

    /**
     * Test if domain is valid with different methods
     * @param  string $url Url to check
     * @return boolean
     */
    public function isBroken($url)
    {
        // Test if URL is internal and page exist
        if ($this->isInternal($url)) {
            return false;
        }

        // Validate domain name
        $urlParts = parse_url($url);
        if (!empty($urlParts['host']) && !$this->isValidDomainName($urlParts['host'])) {
            return true;
        }

        // Finally test if domain is available
        return !$this->isDomainAvailable($url);
    }

    /**
     * Test if domain name is valid
     * @param string $domainName Url to check
     * @return bool
     */
    public function isValidDomainName($domainName)
    {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainName)
            && preg_match("/^.{1,253}$/", $domainName)
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainName));
    }

    /**
     * Test if domain is available with curl
     * @param string $url Url to check
     * @return bool
     */
    public function isDomainAvailable($url)
    {
        // Init curl
        $curlInit = curl_init($url);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
        // Get the response
        $response = curl_exec($curlInit);
        curl_close($curlInit);

        return $response ? true : false;
    }

    /**
     * Test if URL is internal and page exist
     * @param string $url Url to check
     * @return bool
     */
    public function isInternal($url)
    {
        // Check if post exist by url (only works with 'public' posts)
        $postId = url_to_postid($url);
        if ($postId > 0) {
            return true;
        }

        // Check if the URL is internal or external
        $siteUrlComponents = parse_url(get_site_url());
        $urlComponents = parse_url($url);
        if (!empty($siteUrlComponents['host']) && !empty($urlComponents['host']) && strcasecmp($urlComponents['host'], $siteUrlComponents['host']) === 0) {
            // Test with get_page_by_path() to get other post statuses
            $postTypes = get_post_types(array('public' => true));
            if (!empty($urlComponents['path']) && !empty(get_page_by_path($urlComponents['path'], ARRAY_A, $postTypes))) {
                return true;
            }
        }

        return false;
    }
}
