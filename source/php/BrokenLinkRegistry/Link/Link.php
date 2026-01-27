<?php

declare(strict_types=1);

namespace BrokenLinkDetector\BrokenLinkRegistry\Link;

use BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink\Classify;
use BrokenLinkDetector\Config\Config;
use WpService\WpService;

class Link implements LinkInterface
{
    public ?Classify $classification = null;
    public ?bool $isBroken = null;
    public ?bool $isInternal = null;
    public ?bool $isExternal = null;

    private static WpService $wpService;
    private static Config $config;

    private function __construct(
        public string $url,
        public ?int $httpCode,
        public int $postId,
    ) {}

    /**
     * Classify the link as internal or external.
     * This function also interprets the http code
     * to determine if the link is broken.
     *
     * @return Classify
     */
    public function classify(): Classify
    {
        $this->classification = Classify::factory(
            $this->url,
            $this->httpCode,
            self::$wpService,
            self::$config,
        );

        if (!is_null($this->classification->getHttpCode())) {
            $this->httpCode = $this->classification->getHttpCode();
            $this->isBroken = $this->classification->isBroken();
        }

        $this->isInternal = $this->classification->isInternal();
        $this->isExternal = $this->classification->isExternal();

        return $this->classification;
    }

    /**
     * Factory method to create a new instance of a link.
     *
     * @param string $url           The url of the link
     * @param int $httpCode         The http code of the link, if known, otherwise null
     * @param int $postId           The post id where the link was found
     * @param WpService $wpService  The wp service instance
     * @param Config $config        The config instance
     *
     * @return Link
     */
    public static function createLink(string $url, ?int $httpCode, null|string|int $postId, WpService $wpService, Config $config): Link
    {
        self::$wpService = $wpService;
        self::$config = $config;
        return new Link($url, $httpCode, $postId);
    }
}

/*
 * Usage: Add call to classify to run the classification logic
 * (is internal, external, broken by http request or internal post status)
 *
 * $link = Link::createLink('https://www.google.com', 200, 1, $wpService, $config)->classify();
 *
 */
