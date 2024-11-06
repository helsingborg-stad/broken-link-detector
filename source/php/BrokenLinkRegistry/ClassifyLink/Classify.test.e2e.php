<?php

namespace BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink;

use BrokenLinkDetector\Config\Config;
use WP_UnitTestCase;
use WpService\Implementations\NativeWpService;

class ClassifyTest extends WP_UnitTestCase
{
  public function testRemoteGet() {
    $wpService = new NativeWpService();
    $config = new Config($wpService, 'filterPrefix', 'pluginPath', 'pluginUrl');
    $classified = Classify::factory('https://www.google.com/', null, $wpService, $config);

      $this->assertEquals(true, $classified->isExternal());
      $this->assertEquals(false, $classified->isBroken());
      $this->assertEquals(200, $classified->getHttpCode());
  }
}