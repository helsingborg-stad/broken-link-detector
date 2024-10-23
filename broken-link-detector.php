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

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('BROKENLINKDETECTOR_PATH', plugin_dir_path(__FILE__));
define('BROKENLINKDETECTOR_URL', plugins_url('', __FILE__));
define('BROKENLINKDETECTOR_TEMPLATE_PATH', BROKENLINKDETECTOR_PATH . 'templates/');

require_once __DIR__ . '/source/php/Vendor/admin-notice-helper.php';

/*
 * Composer autoload
 */
if (file_exists(BROKENLINKDETECTOR_PATH . 'vendor/autoload.php')) {
    require BROKENLINKDETECTOR_PATH . '/vendor/autoload.php';
}

// Start application
$brokenLinkDetectorApp = new BrokenLinkDetector\App(
    new NativeWpService(),
    new NativeAcfService()
);

register_activation_hook(__FILE__, '\BrokenLinkDetector\App::install');
register_deactivation_hook(__FILE__, '\BrokenLinkDetector\App::uninstall');
