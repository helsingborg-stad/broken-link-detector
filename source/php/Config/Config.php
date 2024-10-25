<?php 

namespace BrokenLinkDetector\Config;

class Config implements ConfigInterface
{
  public function __construct(private $wpService, private string $filterPrefix = 'BrokenLinkDetector/Config'){}

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
      BROKENLINKDETECTOR_URL
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
      BROKENLINKDETECTOR_PATH
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
      BROKENLINKDETECTOR_TEMPLATE_PATH
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
   * Create a prefix for image conversion filter.
   *
   * @return string
   */
  public function createFilterKey(string $filter = ""): string
  {
      return $this->filterPrefix . "/" . ucfirst($filter);
  }
}