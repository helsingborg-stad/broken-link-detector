<?php

namespace BrokenLinkDetector\Admin\Settings;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use BrokenLinkDetector\Admin\Settings\SettingsPage;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use AcfService\Implementations\FakeAcfService;

class SettingsPageTest extends TestCase
{
    private FakeWpService $wpService;
    private FakeAcfService $acfService;
    private SettingsPage $settingsPage;

    protected function setUp(): void
    {
        $this->wpService = new FakeWpService([
            'addAction' => function($hook, $callback) {
                return true;
            },
            '__' => function($text, $domain) {
                return $text;
            }
        ]);

        $this->acfService = new FakeAcfService([
            'addOptionsPage' => function($args) {
                return true;
            }
        ]);

        $this->settingsPage = new SettingsPage(
            $this->wpService,
            $this->acfService,
            []
        );
    }

    /**
     * @testdox addHooks registers acf/init action
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
            }
        ]);

        $acfService = new FakeAcfService([
            'addOptionsPage' => function($args) {
                return true;
            }
        ]);

        $settingsPage = new SettingsPage($wpService, $acfService, []);
        $settingsPage->addHooks();

        $this->assertTrue($actionCalled);
        $this->assertEquals('acf/init', $registeredHook);
        $this->assertEquals([$settingsPage, 'registerSettingsPage'], $registeredCallback);
    }

    /**
     * @testdox registerSettingsPage creates ACF options page
     */
    public function testRegisterSettingsPage(): void
    {
        $optionsPageCalled = false;
        $pageArgs = null;

        $acfService = new FakeAcfService([
            'addOptionsPage' => function($args) use (&$optionsPageCalled, &$pageArgs) {
                $optionsPageCalled = true;
                $pageArgs = $args;
                return true;
            }
        ]);

        $settingsPage = new SettingsPage($this->wpService, $acfService, []);
        $settingsPage->registerSettingsPage();

        $this->assertTrue($optionsPageCalled);
        $this->assertIsArray($pageArgs);
        $this->assertEquals('broken-links-settings', $pageArgs['menu_slug']);
        $this->assertEquals('Broken Links Settings', $pageArgs['page_title']);
        $this->assertEquals('Broken Links Settings', $pageArgs['menu_title']);
        $this->assertEquals('administrator', $pageArgs['capability']);
        $this->assertEquals('options-general.php', $pageArgs['parent_slug']);
        $this->assertTrue($pageArgs['active']);
        $this->assertTrue($pageArgs['redirect']);
        $this->assertEquals('options', $pageArgs['post_id']);
        $this->assertTrue($pageArgs['autoload']);
        $this->assertEquals('Update', $pageArgs['update_button']);
        $this->assertEquals('Settings updated', $pageArgs['updated_message']);
    }

    /**
     * @testdox addHooks registers additional hooks
     */
    public function testAddHooksRegistersAdditionalHooks(): void
    {
        $additionalHookCalled = false;
        
        $additionalHook = new class($additionalHookCalled) implements Hookable {
            private $hookCalled;
            
            public function __construct(&$hookCalled) {
                $this->hookCalled = &$hookCalled;
            }
            
            public function addHooks(): void {
                $this->hookCalled = true;
            }
        };

        $settingsPage = new SettingsPage($this->wpService, $this->acfService, [$additionalHook]);
        $settingsPage->addHooks();

        $this->assertTrue($additionalHookCalled);
    }

    /**
     * @testdox registerAdditionalHooks throws exception for invalid hook
     */
    public function testRegisterAdditionalHooksThrowsExceptionForInvalidHook(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected instance of Hookable, got string');

        $settingsPage = new SettingsPage($this->wpService, $this->acfService, ['invalid_hook']);
        $settingsPage->addHooks();
    }

    /**
     * @testdox SettingsPage works with empty additional hooks
     */
    public function testSettingsPageWorksWithEmptyAdditionalHooks(): void
    {
        $settingsPage = new SettingsPage($this->wpService, $this->acfService, []);
        $settingsPage->addHooks();

        // Should not throw any exception
        $this->assertInstanceOf(SettingsPage::class, $settingsPage);
    }

    /**
     * @testdox SettingsPage integrates with WordPress services correctly
     */
    public function testSettingsPageIntegration(): void
    {
        $this->assertInstanceOf(SettingsPage::class, $this->settingsPage);
        
        // Test that the page can be registered without errors
        $this->settingsPage->registerSettingsPage();
        
        // Test that hooks can be added without errors
        $this->settingsPage->addHooks();
    }

    /**
     * @testdox SettingsPage handles multiple additional hooks correctly
     */
    public function testSettingsPageHandlesMultipleAdditionalHooks(): void
    {
        $hook1Called = false;
        $hook2Called = false;
        
        $hook1 = new class($hook1Called) implements Hookable {
            private $hookCalled;
            
            public function __construct(&$hookCalled) {
                $this->hookCalled = &$hookCalled;
            }
            
            public function addHooks(): void {
                $this->hookCalled = true;
            }
        };

        $hook2 = new class($hook2Called) implements Hookable {
            private $hookCalled;
            
            public function __construct(&$hookCalled) {
                $this->hookCalled = &$hookCalled;
            }
            
            public function addHooks(): void {
                $this->hookCalled = true;
            }
        };

        $settingsPage = new SettingsPage($this->wpService, $this->acfService, [$hook1, $hook2]);
        $settingsPage->addHooks();

        $this->assertTrue($hook1Called);
        $this->assertTrue($hook2Called);
    }
}