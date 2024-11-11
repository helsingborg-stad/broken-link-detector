<?php

namespace BrokenLinkDetector\Admin;

use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\WpService;
use BrokenLinkDetector\Config\Config;

class Editor implements Hookable
{
    public function __construct(private WpService $wpService, private Config $config)
    {
    }

    public function addHooks(): void
    {
        //$this->wpService->addAction('mce_external_plugins', array($this, 'registerMcePlugin'));
        //$this->wpService->addAction('admin_footer', array($this, 'getBrokenLinks'));
    }

    /**
     * Check if the current page is a single admin page
     * @return bool
     */
    private function isSingleAdminPage(): bool
    {
        global $post;
        if (is_admin() && isset($post) && !empty($post->ID)) {
            return true;
        }
        return false;
    }

    /**
     * Register the mce plugin
     * @param array $plugins
     * @return array
     */
    public function registerMcePlugin($plugins): array
    {
        if ($this->isSingleAdminPage()) {
            $plugins['brokenlinksdetector'] = $this->config->getPluginUrl() . '/dist/js/mce-broken-link-detector.min.js';
        }
        return $plugins;
    }

    /**
     * Get broken links for the current post
     * 
     * @return void
     */
    public function getBrokenLinks()
    {
        if(!$this->isSingleAdminPage()) {
            return;
        }

        global $post;

        $urls = \BrokenLinkDetector\ListTable::getBrokenLinks($post->ID);

        echo '<script>
                var broken_links = [
        ';

        $count = 0;
        foreach ($urls as $item) {
            echo "'" . $item->url . "'," . "\n";
            $count++;
        }

        echo '];</script>';
    }
}
