<?php

namespace BrokenLinkDetector\LinkUpdater;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use AcfService\Implementations\FakeAcfService;

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
      $acfService = new FakeAcfService([]);

      $config = new Config(
          $wpService,
          $acfService,
          'filter-prefix',
          'plugin-path',
          'plugin-url'
      );

      $postId = 123;
      
      $linkUpdater = new LinkUpdater(
          $wpService,
          $config,
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
  public function testThatLinkHasChangedDetectsLinksThatChanges()
  {
    // Arrange
    $wpService = new FakeWpService([
      'getPermalink' => 'https://example.com/old-permalink/',
      'getOption' => 'option',
      'applyFilters' => function($filter, $value) {
        return $value;
      }
    ]);

    $acfService = new FakeAcfService([]);

    $config = new Config(
        $wpService,
        $acfService,
        'filter-prefix',
        'plugin-path',
        'plugin-url'
    );
    
    $linkUpdater = new LinkUpdater(
        $wpService,
        $config,
        new Database(
            $config,
            $wpService
        )
    );

    $data = [
      'post_name' => 'sample-post',
      'post_type' => 'post'
    ];

    $post = [
      'ID' => 123,
      'post_name' => 'old-post',
      'post_type' => 'post'
    ];

    $result = $linkUpdater->linkHasChanged($data, $post);

    // Assert
    $this->assertTrue($result);
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