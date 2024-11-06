<?php

namespace BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;
use BrokenLinkDetector\Config\ConfigInterface;

class ClassifyTest extends TestCase
{
  /**
   * @testdox Test that link classification works
   * 
   * @dataProvider uriProvider
   */
  public function testLinkClassification($input, $expected): void
  {
      $wpService = new FakeWpService([
        'getPermalink' => 'https://example.com/old-permalink/',
        'isWpError' => false,
        'siteUrl' => 'https://example.com',
        'applyFilters' => function($filter, $value) {
          return $value;
        },
        'wpRemoteGet' => function($url) {
          if ($url === 'https://this-domain-does-not-exists.tdl') {
            return [
              'response' => [
                'code' => 503
              ]
            ];
          }
          return [
            'response' => [
              'code' => 200
            ]
          ];
        },
        'wpRemoteRetrieveResponseCode' => function($response) {
          return $response['response']['code'];
        }
      ]);

      $config = new Config($wpService, 'filterPrefix', 'pluginPath', 'pluginUrl');

      $link = Link::createLink(
        $input,
        null,
        1,
        $wpService,
        $config
      );
      $link->classify();

      $result = $link->classification->isBroken();

      // Assert
      $this->assertEquals($expected, $result);
  }

  /**
   * Test urls.
   */
  private function uriProvider()
  {
      return [
          ['https://scb.se/', false],
          ['https://this-domain-does-not-exists.tdl', true],
          ['https://google.com', false]
      ];
  }
}