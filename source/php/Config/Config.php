<?php 

namespace BrokenLinkDetector\Config;

use AcfService\Contracts\GetField;
use WpService\Contracts\ApplyFilters;
use WpService\Contracts\__;

class Config implements ConfigInterface
{
  public function __construct(
    private ApplyFilters&__ $wpService, 
    private GetField $acfService,
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
   * Get location of fields
   * 
   * @return string
   */
  public function getPluginFieldsPath(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      $this->getPluginPath() . 'source/fields'
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
   * Get the interval for rechecking broken links.
   * 
   * @return int  The interval in minutes
   */
  public function getRecheckInterval(): int
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      60 * 12
    );
  }

  /**
   * Get the domains that should not be checked if broken or not. 
   * These will get registered, but will always be null.
   * 
   * @return array
   */
  public function getDomainsThatShouldNotBeChecked(): array
  {
    $domains = $this->acfService->getField(
      'broken_links_local_domains',
      'option'
    ) ?: [];

    $domains = array_column($domains, 'domain');
    
    $domains = array_map(function($domain) {
      return parse_url($domain, PHP_URL_HOST);
    }, $domains);

    $domains = array_filter($domains);

    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      $domains ?? []
    );
  }

  /**
   * Get the context check enabled status.
   * 
   * @return bool
   */
  public function isContextCheckEnabled() : bool {
    $isEnabled = $this->acfService->getField(
        'broken_links_context_check_enabled',
        'option'
    ) ?: false;

    $hasUrl = $this->getContextCheckUrl() ? true : false;

    return $this->wpService->applyFilters(
        $this->createFilterKey(__FUNCTION__),
        ($isEnabled && $hasUrl) ?: false
    );
  }
  
  /**
   * Get the URL to probe for the context check.
   * 
   * @return string
   */
  public function getContextCheckUrl() : string {
      $url = $this->acfService->getField(
          'broken_links_context_check_url',
          'option'
      ) ?: false;

      return $this->wpService->applyFilters(
          $this->createFilterKey(__FUNCTION__),
          $url ?: ''
      );
  }

  /**
   * Get the timeout for the context check.
   * 
   * @return int  The timeout in milliseconds
   */
  public function getContextCheckTimeout() : int {
    return $this->wpService->applyFilters(
        $this->createFilterKey(__FUNCTION__),
        3000
    );
  }

  /**
   * Get the domains that should be disabled when context failes.
   * 
   * @return array
   */
  public function getContextCheckDomainsToDisable(): array
  {
    $domains = $this->getDomainsThatShouldNotBeChecked();
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      $domains ?? []
    );
  }

  /**
   * Get the class for the context check success.
   * 
   * @return string
   */
  public function getContextCheckSuccessClass() : string {
      return $this->wpService->applyFilters(
          $this->createFilterKey(__FUNCTION__),
          'context-check-avabile'
      );
  }

  /**
   * Get the class for the context check failed.
   * 
   * @return string
   */
  public function getContextCheckFailedClass() : string {
    return $this->wpService->applyFilters(
        $this->createFilterKey(__FUNCTION__),
        'context-check-unavabile'
    );
  }

  /**
   * Get the tooltip text for the context detection disabled link.
   * 
   * @return string
   */
  public function getContextCheckTooltipText(): string
  {
    $dbLabel = $this->acfService->getField(
      'broken_links_context_tooltip',
      'option'
    ) ?: false;

    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      $dbLabel ?: $this->wpService->__(
        'Link unavabile',
        'broken-link-detector'
      )
    );
  }

  /**
   * Get the namespace for the WP CLI command.
   * 
   * @return string
   */
  public function getCommandNamespace(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      'broken-link-detector'
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