<?php

/**
 * Plugin Name:       Broken Link Detector
 * Description:       Detects and fixes (if possible) broken links in post_content
 * Version: 4.1.0
 * Author:            Sebastian Thulin
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       broken-link-detector
 * Domain Path:       /languages
 */

use AcfService\Implementations\NativeAcfService;
use WpService\Implementations\NativeWpService;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry;
use BrokenLinkDetector\Cli\CommandRunner;

/* Assets */ 
use WpService\FileSystem\BaseFileSystem;
use WpService\FileSystemResolvers\ManifestFilePathResolver;
use WpService\FileSystemResolvers\UrlFilePathResolver;
use WpService\Implementations\FilePathResolvingWpService;
use WpService\Implementations\WpServiceLazyDecorator;
use WpService\Implementations\WpServiceWithTextDomain;

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
$wpService                = new NativeWpService();
$manifestFileWpService    = new WpServiceLazyDecorator();
$urlFilePathResolver      = new UrlFilePathResolver($manifestFileWpService);
$baseFileSystem           = new BaseFileSystem();

$acfService = new NativeAcfService();

$config     = new Config(
    $wpService, 
    $acfService,
    'BrokenLinkDetector/Config',
    $wpService->pluginDirPath(__FILE__),
    $wpService->pluginsUrl('', __FILE__)
);

$manifestFilePathResolver = new ManifestFilePathResolver(
    $config->getPluginPath() . "dist/manifest.json", 
    $baseFileSystem, 
    $manifestFileWpService, 
    $urlFilePathResolver
);

$wpService = new FilePathResolvingWpService(
    new NativeWpService(), 
    $manifestFilePathResolver
);

$manifestFileWpService->setInner(new WpServiceWithTextDomain($wpService, $config->getTextDomain()));

$database   = new Database($config, $wpService);
$registry   = new ManageRegistry($database, $config);
$cliRunner  = new CommandRunner($wpService, $config);

/**
 * Run the plugin
 */
$brokenLinkDetectorApp = new BrokenLinkDetector\App(
    $manifestFileWpService,
    $acfService,
    $database,
    $registry,
    $config,
    $cliRunner
);