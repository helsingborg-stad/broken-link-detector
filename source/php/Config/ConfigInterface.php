<?php 

namespace BrokenLinkDetector\Config;

use AcfService\AcfService;
use WpService\WpService;

interface ConfigInterface
{
  public function __construct(
    WpService $wpService, 
    AcfService $acfService,
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
  public function getRecheckInterval(): int; //The minimum time between rechecking a link

  /* DNS lookup */
  public function checkIfDnsRespondsBeforeProbingUrl(): bool;

  /* Http polling */ 
  public function getMaxRedirects(): int;
  public function getTimeout(): int;
}