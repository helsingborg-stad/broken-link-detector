<?php

namespace BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink;

use BrokenLinkDetector\Config\Config;
use WP_UnitTestCase;
use WpService\Implementations\NativeWpService;

class ClassifyTest extends WP_UnitTestCase
{
  /**
   * @testdox External working URL should return 200
   * @dataProvider externalSuccessfullUrlProvider
   */
  public function testSuccessRemoteFul($url) {
    $wpService = new NativeWpService();
    $config = new Config($wpService, 'filterPrefix', 'pluginPath', 'pluginUrl');
    $classified = Classify::factory($url, null, $wpService, $config);

    $this->assertEquals(200, $classified->getHttpCode());
    $this->assertEquals(true, $classified->isExternal());
    $this->assertEquals(false, $classified->isBroken());
  }

  private function externalSuccessfullUrlProvider():array {
    return [
      ['http://scb.se/'],
      ['https://www.scb.se/'],
      ['https://google.com/'],
      ['https://www.naturvardsverket.se/'],
    ];
  }
}