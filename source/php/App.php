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
use BrokenLinkDetector\Cli\ClassifyLinks;

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
        Config $config,
        CommandRunner $cliRunner
    )
    {
       
        /**
         * Register activation and deactivation hooks
        */
        if (Feature::factory('installer')->isEnabled(1)) {
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
        if (Feature::factory('language')->isEnabled(1)) {
            $loadTextDomain = new \BrokenLinkDetector\TextDomain(
                $wpService,
                $config
            );
            $loadTextDomain->addHooks();
        }

        /**
         * Settings page to configure the plugin
         * 
         * @capability: administrators
        */
        if (Feature::factory('admin_settings')->isEnabled(1)) {
            $registerAdminSettingsPage = new \BrokenLinkDetector\Admin\Settings\SettingsPage(
                $wpService,
                $acfService,
                [
                    new SanitizeLocalDomainSetting($wpService, $acfService)
                ]
            );
            $registerAdminSettingsPage->addHooks();
        }

        /**
         * Summary page to view a list of broken links
         * 
         * @capability: editors
         */
        if (Feature::factory('admin_summary')->isEnabled(1)) {
            $registerAdminSettingsPage = new \BrokenLinkDetector\Admin\Summary\OptionsPage(
                $wpService,
                $db,
                $config
            );
            $registerAdminSettingsPage->addHooks();
        }

        /**
         * Add MCE editor interface to highlight broken links
        */
        if (Feature::factory('admin_highlight_links')->isEnabled(1)) {
            $editorHighlightAsset = new \BrokenLinkDetector\Asset\EditorHighlight(
                $wpService,
                $config,
                $registry
            );
            $editorHighlightAsset->addHooks();
        }

        /** 
         * Loads editor and options fields 
        */
        if (Feature::factory('field_loader')->isEnabled(1)) {
            $fieldLoader = new \BrokenLinkDetector\Fields\AcfExportManager\RegisterFieldConfiguration(
                $wpService,
                $config->getPluginFieldsPath()
            );
            $fieldLoader->addHooks();
        }

        /**
         * Register internal link updater
        */
        if (Feature::factory('fix_internal_links')->isEnabled(1)) {
            $internalLinkUpdater = new \BrokenLinkDetector\LinkUpdater\LinkUpdater(
                $wpService,
                $config,
                $db,
                $registry
            );
            $internalLinkUpdater->addHooks();
        }

        /**
         * Register link registry maintner
         * This will delete links in the registry connected to posts.
         * It will also add links to the registry when a post is saved as new unclassified links. 
         * A reclasification of the links will need to be done to update the status of the links.
        */
        if (Feature::factory('maintain_link_registry')->isEnabled(1)) {
            $findLinksOnSavePost = new \BrokenLinkDetector\Hooks\MaintainLinkRegistryOnSavePost(
                $wpService,
                $config,
                $db,
                $registry
            );
            $findLinksOnSavePost->addHooks();
        }

        /** 
         * Cli commands
         */
        if(Feature::factory('cli')->isEnabled(1)) {
            
            //Commands for database management
            if (Feature::factory('cli_installer')->isEnabled(1)) {
                $installer  = new \BrokenLinkDetector\Installer(
                    $wpService,
                    $config,
                    $db
                );

                $cliRunner->addCommand(new \BrokenLinkDetector\Cli\Database(
                    $wpService,
                    $config,
                    $installer
                ))->registerWithWPCLI();
            }

            // Commands for finding and registering links
            if(Feature::factory('cli_link_finder')->isEnabled(1)) {
                $cliRunner->addCommand(new \BrokenLinkDetector\Cli\FindLinks(
                    $wpService,
                    $config,
                    $db,
                    $registry
                ))->registerWithWPCLI();
            }
        
            //Commands for classifying links
            if(Feature::factory('cli_link_classifier')->isEnabled(1)) {
                $cliRunner->addCommand(new \BrokenLinkDetector\Cli\ClassifyLinks(
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
        if (Feature::factory('context_detection')->isEnabled(1) && $config->isContextCheckEnabled()) {
            $contextDetectionAsset = new \BrokenLinkDetector\Asset\ContextDetection(
                $wpService,
                $config
            );
            $contextDetectionAsset->addHooks();
        }
    }
}
