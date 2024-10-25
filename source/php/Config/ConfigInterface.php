<?php 

namespace BrokenLinkDetector\Config;

interface ConfigInterface
{
  public function __construct(
    $wpService, 
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