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

  public function externalSuccessfullUrlProvider():array {
    return [
      ['http://scb.se/'],
      ['https://www.scb.se/'],
      ['https://google.com/'],
      ['https://www.naturvardsverket.se/'],
      ['https://www.google.com/maps/d/u/0/?amp%3Bie=UTF8&amp%3Bll=56.034842%2C12.732468&amp%3Bmsa=0&amp%3Bmsid=208282319363100350016.00046a3e07a0c84e53bb7&amp%3Boe=UTF8&amp%3Bsource=embed&amp%3Bsp'],
      ['https://www.facebook.com/tonicaorkestern'],
    ];
  }
}