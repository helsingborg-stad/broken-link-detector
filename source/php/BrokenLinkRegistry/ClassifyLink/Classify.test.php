<?php

declare(strict_types=1);

namespace BrokenLinkDetector\BrokenLinkRegistry\ClassifyLink;

use AcfService\Implementations\FakeAcfService;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;
use BrokenLinkDetector\Config\Config;
use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

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
            'applyFilters' => static function ($filter, $value) {
                return $value;
            },
            'wpRemoteGet' => static function ($url) {
                if ($url === 'https://this-domain-does-not-exists.tdl') {
                    return [
                        'response' => [
                            'code' => 503,
                        ],
                    ];
                }
                return [
                    'response' => [
                        'code' => 200,
                    ],
                ];
            },
            'wpRemoteRetrieveResponseCode' => static function ($response) {
                return $response['response']['code'];
            },
        ]);

        $acfService = new FakeAcfService([
            'get_field' => [
                'broken_links_local_domains' => ['https://example.com'],
            ],
        ]);

        $config = new Config($wpService, $acfService, 'filterPrefix', 'pluginPath', 'pluginUrl');

        $link = Link::createLink(
            $input,
            null,
            1,
            $wpService,
            $config,
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
            ['https://scb.se/',                         false],
            ['https://this-domain-does-not-exists.tdl', true],
            ['https://google.com',                      false],
        ];
    }
}
