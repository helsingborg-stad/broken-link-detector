<?php 

/**
 * Classify an URL as internal or external.
 */

namespace BrokenLinkDetector\Detect;

use WpService\WpService;

class Classify implements ClassifyInterface {

  private function __construct(private string $url, private WpService $wpService) {}

  /**
   * Check if the URL is internal.
   * 
   * @return bool
   */
  public function isInternal(): bool 
  {
    return $this->getSiteDomain() === $this->getUrlDomain();
  }

  /**
   * Check if the URL is external.
   * 
   * @return bool
   */
  public function isExternal(): bool 
  {
    return $this->getSiteDomain() !== $this->getUrlDomain();
  }

  /**
   * Factory method to create a new instance if url classification
   * 
   * @param string $url
   * @param WpService $wpService
   * @return Classify
   */
  public static function factory(string $url, WpService $wpService): Classify {
    return new self($url, $wpService);
  }

  /**
   * Get the domain of the URL.
   * 
   * @return string
   */
  private function getUrlDomain(): string 
  {
    return $this->getHostName($this->url);
  }

  /**
   * Get the domain of the site.
   * 
   * @return string
   */
  private function getSiteDomain(): string 
  {
    static $siteUrl;
    if(is_null($siteUrl)) {
      $siteUrl = $this->wpService->siteUrl();
    }
    return $this->getHostName($siteUrl);
  }

  /**
   * Extract the hostname from a URL.
   * 
   * @param string $url
   */
  private function getHostName(string $url): string 
  {
    return parse_url($url, PHP_URL_HOST);
  }
}