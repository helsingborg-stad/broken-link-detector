<?php

namespace BrokenLinkDetector\LinkUpdater;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;


class LinkUpdaterTest extends TestCase
{
  /**
   * @testdox Test that createPermalink generates a correct permalink based on postName
   * 
   * @dataProvider uriProvider
   */
  public function testCreatePermalink($input, $expected, $postName): void
  {
      // Arrange
      $wpService = new FakeWpService([
        'getPermalink' => $input,
        'getOption' => 'option'
      ]);
      $config = new Config(
          $wpService,
          'filter-prefix',
          'plugin-path',
          'plugin-url'
      );

      $postId = 123;
      
      $linkUpdater = new LinkUpdater(
          $wpService,
          $config ,
          new Database(
              $config,
              $wpService
          )
      );

      // Act
      $result = $linkUpdater->createPermalink($postId, $postName);

      // Assert
      $this->assertEquals($expected, $result);
  }

  /**
   * Test urls.
   */
  private function uriProvider()
  {
      return [
          ['https://example.com/old-permalink/', 'https://example.com/sample-post', 'sample-post'],
          ['https://example.com/old-permalink', 'https://example.com/sample-post', 'sample-post'],
          ['https://example.com/old-permalink/with/multiple/subs', 'https://example.com/old-permalink/with/multiple/sample-post', 'sample-post']
      ];
  }
}