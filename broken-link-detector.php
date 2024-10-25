<?php

/**
 * Plugin Name:       Broken Link Detector
 * Plugin URI:        (#plugin_url#)
 * Description:       Detects and fixes (if possible) broken links in post_content
 * Version: 3.0.6
 * Author:            Kristoffer Svanmark
 * Author URI:        (#plugin_author_url#)
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       broken-link-detector
 * Domain Path:       /languages
 */

use AcfService\Implementations\NativeAcfService;
use WpService\Implementations\NativeWpService;
use BrokenLinkDetector\Database;
use BrokenLinkDetector\Config\Config;
use WpService\Contracts\PluginDirPath;
use WpService\Contracts\PluginsUrl;

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

// TODO: Remove this when the plugin is refactored
require_once __DIR__ . '/source/php/Vendor/admin-notice-helper.php';

/* Autoload files */
if (file_exists($wpService->pluginDirPath(__FILE__) . '/vendor/autoload.php')) {
    require $wpService->pluginDirPath(__FILE__) . '/vendor/autoload.php';
}

//Initialize services
$wpService  = new NativeWpService();
$acfService = new NativeAcfService();
$config     = new Config(
    $wpService, 
    'BrokenLinkDetector/Config',
    $wpService->pluginDirPath(__FILE__),
    $wpService->pluginsUrl('', __FILE__)
);
$database   = new Database($config, $wpService);

// Start application
$brokenLinkDetectorApp = new BrokenLinkDetector\App(
    $wpService,
    $acfService,
    $database,
    $config
);