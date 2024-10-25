<?php 

namespace BrokenLinkDetector\Config;

use WpService\WpService;

interface ConfigInterface
{
  public function __construct(
    WpService $wpService, 
    string $filterPrefix,
    string $pluginPath,
    string $pluginUrl,
  );
  public function getDatabaseVersionKey(): string;
  public function getTableName(): string;
  public function getPluginUrl(): string;
  public function getPluginPath(): string;
  public function getPluginTemplatePath(): string;
  public function getTextDomain(): string;
}