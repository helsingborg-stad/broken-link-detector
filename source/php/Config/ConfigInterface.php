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
  public function getDatabaseVersion(): string;
  public function getTableName(): string;
  public function getPluginUrl(): string;
  public function getPluginPath(): string;
  public function getPluginTemplatePath(): string;
  public function getTextDomain(): string;
  public function linkUpdaterBannedPostTypes(): array;
  public function linkDetectBannedPostTypes(): array;
  public function linkDetectAllowedPostStatuses(): array;

  /* Cli */ 
  public function getCommandNamespace(): string;

  /* Link classification */ 
  public function responseCodesConsideredBroken(): array;

  /* DNS lookup */
  public function checkIfDnsRespondsBeforeProbingUrl(): bool;

  /* Http polling */ 
  public function getMaxRedirects(): int;
  public function getTimeout(): int;
}