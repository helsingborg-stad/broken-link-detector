<?php

namespace BrokenLinkDetector\Link;

use BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink\Classify;
use WpService\WpService;
use BrokenLinkDetector\Config\Config;

class Link implements LinkInterface {
    public Classify $classification;
    private static WpService $wpService;
    private static Config $config;
    private function __construct(public string $url, public ?int $httpCode, public int $postId) {
        $this->classify();
    }
    
    /**
     * Classify the link as internal or external.
     * This function also interprets the http code 
     * to determine if the link is broken.
     * 
     * @return Classify
     */
    public function classify(): Classify {
        return $this->classification = Classify::factory(
            $this->url, 
            $this->httpCode,
            self::$wpService,
            self::$config
        );
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
    public static function createLink(string $url, int $httpCode, int $postId, WpService $wpService, Config $config): Link {
        self::$wpService = $wpService;
        self::$config = $config;
        return new Link($url, $httpCode, $postId);
    }
}