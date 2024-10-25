<?php

/**
 * Plugin Name:       Broken Link Detector
 * Description:       Detects and fixes (if possible) broken links in post_content
 * Version: 3.0.6
 * Author:            Sebastian Thulin
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       broken-link-detector
 * Domain Path:       /languages
 */

use AcfService\Implementations\NativeAcfService;
use WpService\Implementations\NativeWpService;
use BrokenLinkDetector\Database;
use BrokenLinkDetector\Config\Config;

/**
 * If this file is called directly, abort.
 */
if (! defined('WPINC')) {
    die;
}

/**
 * Autoload plugin classes, if dependencies are installed.
 */
try {
    require_once __DIR__ . '/vendor/autoload.php';
} catch (Exception $e) {
    throw new Exception($e->getMessage());
}

/**
 * Bootstrap the plugin
 */
$wpService  = new NativeWpService();
$acfService = new NativeAcfService();
$config     = new Config(
    $wpService, 
    'BrokenLinkDetector/Config',
    $wpService->pluginDirPath(__FILE__),
    $wpService->pluginsUrl('', __FILE__)
);
$database   = new Database($config, $wpService);

/**
 * Run the plugin
 */
$brokenLinkDetectorApp = new BrokenLinkDetector\App(
    $wpService,
    $acfService,
    $database,
    $config
);