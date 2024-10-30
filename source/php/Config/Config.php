<?php 

namespace BrokenLinkDetector\Config;

use WpService\Contracts\ApplyFilters;
use WpService\Contracts\GetOption;

class Config implements ConfigInterface
{
  public function __construct(
    private ApplyFilters&GetOption $wpService, 
    private string $filterPrefix,
    private string $pluginPath,
    private string $pluginUrl
  ){}

  /**
   * Get the key for the database version.
   * 
   * @return string
   */
  public function getDatabaseVersionKey(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      'broken_link_detector_db_version'
    );
  }

  /**
   * Get the current database version from the options table.
   * 
   * @return string|null
   */
  public function getDatabaseVersion(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      '2.0.0'
    );
  }

  /**
   * Get the name of the table that stores broken links.
   * 
   * @return string
   */
  public function getTableName(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      'broken_links_detector'
    );
  }

  /**
   * Get plugin url. 
   * 
   * @return string
   */
  public function getPluginUrl(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      $this->pluginUrl
    );
  }

  /**
   * Get plugin url. 
   * 
   * @return string
   */
  public function getPluginPath(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      $this->pluginPath
    );
  }

  /**
   * Get template path. 
   * 
   * @return string
   */
  public function getPluginTemplatePath(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      $this->getPluginPath() . 'templates'
    );
  }

  /**
   * Get location of fields
   * 
   * @return string
   */
  public function getPluginFieldsPath(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      $this->getPluginPath() . 'fields'
    );
  }

  /**
   * Get text domain
   * 
   * @return string
   */
  public function getTextDomain(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      'broken-link-detector'
    );
  }

  /**
   * Get post types where link repair (link updater) should not run.
   * 
   * @return array
   */
  public function linkUpdaterBannedPostTypes(): array
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      ['attachment', 'revision', 'acf', 'acf-field', 'acf-field-group']
    ) ?? [];
  }

  /**
   * Get post types that should not be checked for broken links.
   * 
   * @return array
   */
  public function linkDetectBannedPostTypes(): array {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      ['attachment', 'revision', 'acf', 'acf-field', 'acf-field-group']
    ) ?? [];
  }

  /**
   * Get post types that should not be checked for broken links.
   * 
   * @return array
   */
  public function linkDetectAllowedPostStatuses(): array {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      ['publish', 'private', 'password']
    ) ?? [];
  }

  /**
   * Get response codes that are considered broken.
   * 
   * @return array
   */
  public function responseCodesConsideredBroken(): array
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      [400, 403, 404, 410, 500, 502, 503, 504]
    ) ?? [];
  }

  /**
   * Get the DNS record types to check.
   * 
   * @return array
   */
  public function checkIfDnsRespondsBeforeProbingUrl(): bool
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      true
    );
  }

  /**
   * Get the number of redirects to follow.
   * 
   * @return int
   */
  public function getMaxRedirects(): int
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      5
    );
  }

  /**
   * Get the timeout for the request.
   * 
   * @return int
   */
  public function getTimeout(): int
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      5
    );
  }

  /**
   * Create a prefix for image conversion filter.
   *
   * @return string
   */
  public function createFilterKey(string $filter = ""): string
  {
    return $this->filterPrefix . "/" . ucfirst($filter);
  }
}