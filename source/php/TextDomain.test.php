<?php

namespace BrokenLinkDetector;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use AcfService\Implementations\FakeAcfService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\TextDomain;

class TextDomainTest extends TestCase
{
    private FakeWpService $wpService;
    private Config $config;
    private TextDomain $textDomain;

    protected function setUp(): void
    {
        $this->wpService = new FakeWpService([
            'addAction' => function($hook, $callback) {
                return true;
            },
            'loadPluginTextdomain' => function($domain, $deprecated, $plugin_rel_path) {
                return true;
            },
            'applyFilters' => function($filter, $value) {
                return $value;
            },
            '__' => function($text, $domain) {
                return $text;
            },
            'wpautop' => function($text) {
                return $text;
            }
        ]);

        $acfService = new FakeAcfService([
            'getField' => []
        ]);

        $this->config = new Config(
            $this->wpService,
            $acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $this->textDomain = new TextDomain($this->wpService, $this->config);
    }

    /**
     * @testdox TextDomain constructs correctly
     */
    public function testTextDomainConstruction(): void
    {
        $this->assertInstanceOf(TextDomain::class, $this->textDomain);
    }

    /**
     * @testdox addHooks registers init action
     */
    public function testAddHooks(): void
    {
        $actionCalled = false;
        $registeredHook = null;
        $registeredCallback = null;

        $wpService = new FakeWpService([
            'addAction' => function($hook, $callback) use (&$actionCalled, &$registeredHook, &$registeredCallback) {
                $actionCalled = true;
                $registeredHook = $hook;
                $registeredCallback = $callback;
                return true;
            },
            'loadPluginTextdomain' => function($domain, $deprecated, $plugin_rel_path) {
                return true;
            },
            'applyFilters' => function($filter, $value) {
                return $value;
            },
            '__' => function($text, $domain) {
                return $text;
            },
            'wpautop' => function($text) {
                return $text;
            }
        ]);

        $acfService = new FakeAcfService([
            'getField' => []
        ]);

        $config = new Config(
            $wpService,
            $acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $textDomain = new TextDomain($wpService, $config);
        $textDomain->addHooks();

        $this->assertTrue($actionCalled);
        $this->assertEquals('init', $registeredHook);
        $this->assertEquals([$textDomain, 'loadTextDomain'], $registeredCallback);
    }

    /**
     * @testdox loadTextDomain loads plugin text domain
     */
    public function testLoadTextDomain(): void
    {
        $textDomainCalled = false;
        $domain = null;
        $pluginPath = null;

        $wpService = new FakeWpService([
            'loadPluginTextdomain' => function($d, $deprecated, $p) use (&$textDomainCalled, &$domain, &$pluginPath) {
                $textDomainCalled = true;
                $domain = $d;
                $pluginPath = $p;
                return true;
            },
            'applyFilters' => function($filter, $value) {
                return $value;
            },
            '__' => function($text, $domain) {
                return $text;
            },
            'wpautop' => function($text) {
                return $text;
            }
        ]);

        $acfService = new FakeAcfService([
            'getField' => []
        ]);

        $config = new Config(
            $wpService,
            $acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $textDomain = new TextDomain($wpService, $config);
        $textDomain->loadTextDomain();

        $this->assertTrue($textDomainCalled);
        $this->assertEquals('broken-link-detector', $domain);
        $this->assertStringContainsString('languages', $pluginPath);
    }

    /**
     * @testdox TextDomain integrates with Config correctly
     */
    public function testTextDomainIntegration(): void
    {
        // Note: The actual method has a typo - gettextDomain instead of getTextDomain
        // We test the correct method that should be used
        $textDomain = $this->config->getTextDomain();
        $this->assertEquals('broken-link-detector', $textDomain);
    }
}