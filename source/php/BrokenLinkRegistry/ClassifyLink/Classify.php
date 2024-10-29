<?php 

/**
 * Classify an URL as internal or external.
 */

namespace BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink;

use WpService\WpService;
use BrokenLinkDetector\Config\Config;
use WP_Error;

class Classify implements ClassifyInterface {

  private function __construct(private string $url, private ?int $httpCode, private WpService $wpService, private Config $config) {
    //If a http code wasent passed, try to get it
    if(is_null($this->httpCode)) {
      $this->tryGetHttpCode();
    }
  }

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
   * Check if the URL is broken, by provided http codes. 
   */
  public function isBroken(): ?bool 
  {
    $brokenCodes = $this->config->responseCodesConsideredBroken();
    if(in_array($this->httpCode, $brokenCodes)) {
      return true;
    }
    return false;
  }

  /**
   * Try to get the http code of the URL.
   * 
   * @return int|WP_Error   The http code if successful, otherwise WP_Error
   */
  private function tryGetHttpCode(): int|WP_Error
  {
    $response = $this->wpService->wpRemoteGet($this->url, [
      'headers_only' => true,
      'redirection' => $this->config->getMaxRedirects(),
      'timeout' => $this->config->getTimeout(),
    ]);

    if(!$this->wpService->isWpError($response)) {
      $this->httpCode = $this->wpService->wpRemoteRetrieveResponseCode(
        $response
      );
      return $this->httpCode;
    }

    return $response;
  }

  /**
   * Factory method to create a new instance if url classification
   * 
   * @param string $url
   * @param WpService $wpService
   * @return Classify
   */
  public static function factory(string $url, ?int $httpCode, WpService $wpService, Config $config): Classify {
    return new self($url, $httpCode, $wpService, $config);
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