<?php

namespace BrokenLinkDetector;

/* Services */
use WpService\WpService;
use AcfService\AcfService;

/* Config & Features */ 
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Config\Feature;

/* Helpers */ 
use BrokenLinkDetector\Database\Database;

/* Link updater */
use BrokenLinkDetector\LinkUpdater\LinkUpdater;

/* Admin functions */ 
use BrokenLinkDetector\Admin\Editor;

/* Link Registry */
use BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry;
use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLink;
use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostContent;
use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostMeta;

class App
{
    public static $dbTable = 'broken_links_detector';
    public static $installChecked = false;
    public static $wpdb = null;

    public static $externalDetector = false;

    public function __construct(
        WpService $wpService, 
        AcfService $acfService, 
        Database $db, 
        ManageRegistry $registry, 
        Config $config
    )
    {
       
        /**
         * Register activation and deactivation hooks
        */
        if (Feature::factory('installer')->isEnabled()) {
            $registerActivation = new \BrokenLinkDetector\Installer(
                $wpService,
                $config,
                $db
            );
            $registerActivation->addHooks();
        }

        /**
         * Load text domain
        */
        if (Feature::factory('language')->isEnabled()) {
            $loadTextDomain = new \BrokenLinkDetector\TextDomain(
                $wpService,
                $config
            );
            $loadTextDomain->addHooks();
        }

        /**
         * Init settings page
        */
        if (Feature::factory('admin_settings')->isEnabled()) {
            $registerAdminSettingsPage = new \BrokenLinkDetector\Settings\AdminSettingsPage(
                $wpService,
                $acfService
            );
            $registerAdminSettingsPage->addHooks();
        }

        /** 
         * Field loader
        */
        if (Feature::factory('field_loader')->isEnabled()) {
            $fieldLoader = new \BrokenLinkDetector\Fields\AcfExportManager\RegisterFieldConfiguration(
                $wpService,
                $config->getPluginFieldsPath()
            );
            $fieldLoader->addHooks();
        }

        /**
         * Register internal link detector
        */
        if (Feature::factory('fix_internal_links')->isEnabled()) {
            $internalLinkUpdater = new \BrokenLinkDetector\LinkUpdater\LinkUpdater(
                $wpService,
                $config,
                $db,
                $registry
            );
            $internalLinkUpdater->addHooks();
        }

        /**
         * Add editor interface
        */
        if (Feature::factory('highlight_broken_links')->isEnabled()) {
            $editorInterface = new \BrokenLinkDetector\Admin\Editor(
                $wpService,
                $config
            );
            $editorInterface->addHooks();
        }

        /*
        * Link finder
        */

        if (Feature::factory('link_finder')->isEnabled()) {

            /*
            $findLinkFromPostContent = new \BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostContent();
            $findLinkFromPostMeta = new \BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostMeta();

            $linkFinder = new \BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLink(
                $wpService,
                $findLinkFromPostContent,
                $findLinkFromPostMeta,
            );
            $linkFinder->addHooks();*/ 



        }
    }

    

    public function rescanPost()
    {
        echo '<div class="misc-pub-section">
            <label style="display:block;margin-bottom:5px;"><input type="checkbox" name="broken-link-detector-rescan" value="true"> ' . __('Rescan broken links', 'broken-link-detector') . '</label>
            <small>' . __('The rescan will be executed for this post only. The scan will execute direcly after the save is completed and may take a few minutes to complete.', 'broken-links-detector') . '</small>
        </div>';
    }

    /**
     * Sort post if sorting on broken-links column
     * @return void
     */
    public function brokenLinksColumnSorting()
    {
        add_filter('posts_fields', function ($fields, $query) {
            if ($query->get('orderby') !== 'broken-links') {
                return $fields;
            }

            global $wpdb;

            $fields .= ", (
                SELECT COUNT(*)
                FROM " . self::$dbTable . "
                WHERE post_id = {$wpdb->posts}.ID
            ) AS broken_links_count";

            return $fields;
        }, 10, 2);

        add_filter('posts_orderby', function ($orderby, $query) {
            if ($query->get('orderby') !== 'broken-links') {
                return $orderby;
            }

            $orderby = "broken_links_count {$query->get('order')}, " . $orderby;
            return $orderby;
        }, 10, 2);
    }

    /**
     * Add broken links column to post type post list table
     * @return void
     */
    public function postTypeColumns()
    {
        $postTypes = get_post_types();

        foreach ($postTypes as $postType) {
            add_filter('manage_' . $postType . '_posts_columns', function ($columns) {
                broken_link_detector_array_splice_assoc($columns, -1, 0, array(
                    'broken-links' => __('Broken links', 'broken-links-detector')
                ));

                return $columns;
            }, 50);

            add_filter('manage_edit-' . $postType . '_sortable_columns', function ($columns) {
                $columns['broken-links'] = 'broken-links';
                return $columns;
            }, 50);

            add_action('manage_' . $postType . '_posts_custom_column', function ($column, $postId) {
                if ($column !== 'broken-links') {
                    return;
                }

                $links = \BrokenLinkDetector\ListTable::getBrokenLinksCount($postId);

                if ($links > 0) {
                    echo '<span class="broken-link-detector-label">' . $links . '</span>';
                } else {
                    echo '<span aria-hidden="true">—</span>';
                }
            }, 20, 2);
        }
    }

    /**
     * Adds the list table page of broken links
     */
    public function addListTablePage()
    {
        add_submenu_page(
            'options-general.php',
            'Broken links',
            'Broken links',
            'edit_posts',
            'broken-links-detector',
            function () {
                \BrokenLinkDetector\App::checkInstall();

                $listTable = new \BrokenLinkDetector\ListTable();

                $offset = get_option('gmt_offset');

                if ($offset > -1) {
                    $offset = '+' . $offset;
                } else {
                    $offset = '-' . (1 * abs($offset));
                }

                $nextRun = date('Y-m-d H:i', strtotime($offset . ' hours', wp_next_scheduled('broken-links-detector-external')));

                include BROKENLINKDETECTOR_TEMPLATE_PATH . 'list-table.php';
            }
        );
    }

    /**
     * Checks if a saved posts permalink is changed and updates permalinks throughout the site
     * @param  array $data     Post data
     * @param  array $postarr  $_POST data
     * @return array           Post data (do not change)
     */
    public function checkSavedPost($data, $postarr)
    {
        remove_action('wp_insert_post_data', array($this, 'checkSavedPost'), 10, 2);

        $detector = new \BrokenLinkDetector\InternalDetector($data, $postarr);
        return $data;
    }

    /**
     * Remove broken links when deleting a page
     * @param int $postId The post id that is being deleted
     */
    public function deleteBrokenLinks($postId)
    {
        global $wpdb;
        $tableName = self::$dbTable;
        $wpdb->delete($tableName, array('post_id' => $postId), array('%d'));
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {
        wp_enqueue_style('broken-links-detector', BROKENLINKDETECTOR_URL . '/dist/css/broken-link-detector.min.css', '', '1.0.0');
    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {
    }
}
