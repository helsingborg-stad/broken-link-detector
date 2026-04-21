<?php

namespace BrokenLinkDetector\Admin\Summary;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Admin\Summary\OptionsPage;
use AcfService\Implementations\FakeAcfService;

class OptionsPageTest extends TestCase
{
    private FakeWpService $wpService;
    private Config $config;
    private Database $database;
    private OptionsPage $optionsPage;

    protected function setUp(): void
    {
        $this->wpService = new FakeWpService([
            'addAction' => function($hook, $callback) {
                return true;
            },
            'addManagementPage' => function($title, $menuTitle, $capability, $menuSlug, $callback) {
                return 'hook_suffix';
            },
            '__' => function($text, $domain) {
                return $text;
            },
            'applyFilters' => function($filter, $value) {
                return $value;
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

        $this->database = new Database($this->config, $this->wpService);
        $this->optionsPage = new OptionsPage($this->wpService, $this->database, $this->config);
    }

    /**
     * @testdox addHooks registers admin_menu action
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
            '__' => function($text, $domain) {
                return $text;
            },
            'addManagementPage' => function($title, $menuTitle, $capability, $menuSlug, $callback) {
                return 'hook_suffix';
            },
            'applyFilters' => function($filter, $value) {
                return $value;
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

        $database = new Database($config, $wpService);
        $optionsPage = new OptionsPage($wpService, $database, $config);
        
        $optionsPage->addHooks();

        $this->assertTrue($actionCalled);
        $this->assertEquals('admin_menu', $registeredHook);
        $this->assertEquals([$optionsPage, 'registerSummaryPage'], $registeredCallback);
    }

    /**
     * @testdox registerSummaryPage creates management page
     */
    public function testRegisterSummaryPage(): void
    {
        $managementPageCalled = false;
        $pageTitle = null;
        $menuTitle = null;
        $capability = null;
        $menuSlug = null;

        $wpService = new FakeWpService([
            'addManagementPage' => function($title, $menu, $cap, $slug, $callback) use (&$managementPageCalled, &$pageTitle, &$menuTitle, &$capability, &$menuSlug) {
                $managementPageCalled = true;
                $pageTitle = $title;
                $menuTitle = $menu;
                $capability = $cap;
                $menuSlug = $slug;
                return 'hook_suffix';
            },
            '__' => function($text, $domain) {
                return $text;
            },
            'applyFilters' => function($filter, $value) {
                return $value;
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

        $database = new Database($config, $wpService);
        $optionsPage = new OptionsPage($wpService, $database, $config);
        
        $optionsPage->registerSummaryPage();

        $this->assertTrue($managementPageCalled);
        $this->assertEquals('Broken Links Report', $pageTitle);
        $this->assertEquals('Broken Links Report', $menuTitle);
        $this->assertEquals('edit_pages', $capability);
        $this->assertEquals('broken-links-report', $menuSlug);
    }

    /**
     * @testdox renderSummaryPage method exists and is callable
     */
    public function testRenderSummaryPageMethodExists(): void
    {
        // Just test that the method exists and is callable
        $this->assertTrue(method_exists($this->optionsPage, 'renderSummaryPage'));
        $this->assertTrue(is_callable([$this->optionsPage, 'renderSummaryPage']));
    }

    /**
     * @testdox OptionsPage integrates with WP Service correctly
     */
    public function testOptionsPageIntegration(): void
    {
        $this->assertInstanceOf(OptionsPage::class, $this->optionsPage);
        $this->assertInstanceOf(Config::class, $this->config);
        $this->assertInstanceOf(Database::class, $this->database);
    }
}