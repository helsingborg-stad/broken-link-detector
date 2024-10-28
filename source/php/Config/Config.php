<?php 

namespace BrokenLinkDetector\Config;

use WpService\Contracts\ApplyFilters;
use WpService\Contracts\GetOption;

enum Feature: string
{
    case SCAN_BROKEN_LINKS = 'ScanBrokenLinks';
    case LIST_BROKEN_LINKS = 'ListBrokenLinks';
    case FIX_INTERNAL_LINKS = 'FixInternalLinks';
    case HIGHLIGHT_BROKEN_LINKS = 'HighlightBrokenLinks';
}
class Config implements ConfigInterface
{
  public function __construct(
    private ApplyFilters&GetOption $wpService, 
    private string $filterPrefix,
    private string $pluginPath,
    private string $pluginUrl
  ){}

  /**
   * Check if a feature is enabled.
   * 
   * @param Feature $feature
   */
  public function isEnabled(Feature $feature) {

    $defaultEnabled = [
      Feature::SCAN_BROKEN_LINKS => true,
      Feature::LIST_BROKEN_LINKS => true,
      Feature::FIX_INTERNAL_LINKS => true,
      Feature::HIGHLIGHT_BROKEN_LINKS => true,
    ];

    $defaultEnabled = $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      $defaultEnabled
    );

    $isEnabled = $defaultEnabled[$feature] ?? false;

    return $this->wpService->applyFilters(
        $this->createFilterKey(__FUNCTION__ . "/{$feature->value}"),
        $isEnabled
    );
  }

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
   * Get post types that should not be checked for broken links.
   * 
   * @return array
   */
  public function getDisabledLinkReplacementPostTypes(): array
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      ['attachment', 'revision', 'acf', 'acf-field', 'acf-field-group']
    ) ?? [];
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