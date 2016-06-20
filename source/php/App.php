<?php

namespace BrokenLinkDetector;

class App
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        add_filter('wp_insert_post_data', array($this, 'scanSavedPost'), 10, 2);
    }

    public function scanSavedPost($data, $postarr)
    {
        $detector = new \BrokenLinkDetector\InternalDetector($data, $postarr);
        return $data;
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {

    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {

    }
}
