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
use BrokenLinkDetector\Admin\Settings\SanitizeLocalDomainSetting;

/* Link Registry */
use BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry;
use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLink;
use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostContent;
use BrokenLinkDetector\BrokenLinkRegistry\FindLink\FindLinkFromPostMeta;

/* Cli commands */ 
use BrokenLinkDetector\Cli\CommandRunner;
use BrokenLinkDetector\Cli\FindLinks;

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
            $registerAdminSettingsPage = new \BrokenLinkDetector\Admin\Settings\SettingsPage(
                $wpService,
                $acfService,
                [
                    new SanitizeLocalDomainSetting($wpService, $acfService)
                ]
            );
            $registerAdminSettingsPage->addHooks();
        }

        /** Init summary */
        if (Feature::factory('admin_summary')->isEnabled()) {
            $registerAdminSettingsPage = new \BrokenLinkDetector\Admin\Summary\OptionsPage(
                $wpService,
                $db,
                $config
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
         * Register internal link updater
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

        /** 
         * Cli commands
         */
        if(Feature::factory('cli')->isEnabled()) {
            $runner     = new \BrokenLinkDetector\Cli\CommandRunner(
                $wpService,
                $config
            );
            
            //Commands for database management
            if (Feature::factory('installer')->isEnabled()) {
                $installer  = new \BrokenLinkDetector\Installer(
                    $wpService,
                    $config,
                    $db
                );

                $runner->addCommand(new \BrokenLinkDetector\Cli\Database(
                    $wpService,
                    $config,
                    $installer
                ))->registerWithWPCLI();
            }

            $registry = new \BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry(
                $db, 
                $config
            );

            // Commands for finding and registering links
            if(Feature::factory('link_finder')->isEnabled()) {
                $runner->addCommand(new \BrokenLinkDetector\Cli\FindLinks(
                    $wpService,
                    $config,
                    $db,
                    $registry
                ))->registerWithWPCLI();
            }
        
            //Commands for classifying links
            if(Feature::factory('classify_links')->isEnabled()) {
                $runner->addCommand(new \BrokenLinkDetector\Cli\ClassifyLinks(
                    $wpService,
                    $config,
                    $db,
                    $registry
                ))->registerWithWPCLI();
            }

        }

        /**
         * Context detection frontend 
         */
        if (Feature::factory('context_detection')->isEnabled() && $config->isContextCheckEnabled()) {
            $contextDetectionAsset = new \BrokenLinkDetector\Asset\ContextDetection(
                $wpService,
                $config
            );
            $contextDetectionAsset->addHooks();
        }
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
