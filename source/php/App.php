<?php

namespace BrokenLinkDetector;

class App
{
    public static $dbTable = 'broken_links_detector';

    public function __construct()
    {
        add_action('init', array($this, 'dbTableName'));

        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        add_filter('wp_insert_post_data', array($this, 'checkSavedPost'), 10, 2);

        new \BrokenLinkDetector\ExternalDetector();
    }

    public function dbTableName()
    {
        global $wpdb;
        self::$dbTable = $wpdb->prefix . self::$dbTable;
    }

    /**
     * Setsup the database table on plugin activation (hooked in App.php)
     * @return void
     */
    public function install()
    {
        global $wpdb;

        $charsetCollation = $wpdb->get_charset_collate();
        $tableName = self::$dbTable;

        if (!empty(get_site_option('broken-links-detector-db-version')) && $wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName) {
            return;
        }

        $sql = "CREATE TABLE $tableName (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) DEFAULT NULL,
            url varchar(255) DEFAULT '' NOT NULL,
            date_found datetime DEFAULT current_timestamp,
            is_new smallint(1) DEFAULT 1,
            UNIQUE KEY id (id)
        ) $charsetCollation;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('broken-links-detector-db-version', '1.0.0');
    }

    /**
     * Drops the database table on plugin deactivation (hooked in App.php)
     * @return void
     */
    public function uninstall()
    {
        global $wpdb;

        $tableName = self::$dbTable;
        $sql = 'DROP TABLE ' . $tableName;

        $wpdb->query($sql);

        delete_option('broken-links-detector-db-version');
    }

    /**
     * Checks if a saved posts permalink is changed and updates permalinks throughout the site
     * @param  array $data     Post data
     * @param  array $postarr  $_POST data
     * @return array           Post data (do not change)
     */
    public function checkSavedPost($data, $postarr)
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
