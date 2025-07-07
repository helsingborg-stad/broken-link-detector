<?php 

/**
 * Classify an URL as internal or external.
 */

namespace BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink;

use WpService\WpService;
use BrokenLinkDetector\Config\Config;
use WP_Error;
use BrokenLinkDetector\Cli\Log;

class Classify implements ClassifyInterface {

  private static $statusCodeCache = [];

  private function __construct(private string $url, private ?int $httpCode, private WpService $wpService, private Config $config) {

    //If a http code wasent passed, try to get it
    if(is_null($this->httpCode) && $this->shouldClassify($url)) {

      //Check if the URL is already classified in this run
      if(array_key_exists($this->url, self::$statusCodeCache)) {
        $this->httpCode = self::$statusCodeCache[$this->url];
        return;
      }

      if($this->isInternal()) {
        if($this->tryGetHttpCodeByPostStatus() === null) {
          $this->tryGetHttpCodeByUrlResponse();
        }
      } else {
        $this->tryGetHttpCodeByUrlResponse();
      }

      //Store the http code in cache
      if(!is_null($this->httpCode)) {
       self::$statusCodeCache[$this->url] = $this->httpCode;
      }
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
   * Get the http code of the URL.
   */
  public function getHttpCode(): ?int 
  {
    return $this->httpCode;
  }

  /**
   * Try to get the post status of the URL internally.
   * 
   * @return int|null The http code if successful, otherwise null
   */
  private function tryGetHttpCodeByPostStatus(): ?int
  {
    $postId = $this->wpService->urlToPostId($this->url);
    if (!$postId) {
      return null;
    }

    $postStatus = $this->wpService->getPostStatus($postId);
    $postType = $this->wpService->getPostType($postId);

    // Check if post type is allowed (not banned)
    $bannedPostTypes = $this->config->linkDetectBannedPostTypes();
    if (in_array($postType, $bannedPostTypes)) {
      return $this->httpCode = 404; // Post type not allowed
    }

    // Check if post status is allowed
    $allowedPostStatuses = $this->config->linkDetectAllowedPostStatuses();
    if (in_array($postStatus, $allowedPostStatuses)) {
      // Return appropriate HTTP code based on post status
      switch ($postStatus) {
        case 'publish':
          return $this->httpCode = 200;
        case 'private':
          // Private posts may be accessible depending on user permissions
          return $this->httpCode = 200;
        case 'password':
          // Password-protected posts are accessible but require authentication
          return $this->httpCode = 200;
        default:
          return $this->httpCode = 200;
      }
    }

    // Post status not allowed or post not found
    return $this->httpCode = 404;
  }

  /**
   * Try to get the DNS record of the domain.
   * 
   * @return bool True if the DNS record was found, otherwise false
   */
  private function tryGetDnsRecord(): bool
  {
    static $dnsCache = [];

    if (!$this->config->checkIfDnsRespondsBeforeProbingUrl() || !function_exists('dns_get_record')) {
      return true;
    }

    try {
      $domain = $this->getUrlDomain();

      if (isset($dnsCache[$domain])) {
        return $dnsCache[$domain];
      }

      return $dnsCache[$domain] = (bool) dns_get_record($domain, DNS_A) || (bool) dns_get_record($domain, DNS_CNAME);

    } catch(\Exception $e) {
      return true;
    }
  }

  /**
   * Try to get the http code of the URL.
   * 
   * @return int   The http code, or placeholder 503 if not responding/unreachable
   */
  private function tryGetHttpCodeByUrlResponse(): int
  {
    //Check if the URL is reachable
    if(!$this->tryGetDnsRecord()) {
      return $this->httpCode = 503; //Bad gateway, no better code available (not really a http error)
    }

    $response = $this->wpService->wpRemoteGet($this->url, [
      'headers' => [
          'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36',
          "Sec-Fetch-Mode" => "navigate",
      ],
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

    return $this->httpCode = 503; //Not responding
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
   * Check if the URL should be classified.
   * 
   * @param string $url
   * @return bool
   */
  private function shouldClassify(string $url): bool {
    $doNotClassifyDomains = $this->config->getDomainsThatShouldNotBeChecked();
    if(in_array($this->getHostName($url), $doNotClassifyDomains)) {
      return false;
    }
    return true;
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