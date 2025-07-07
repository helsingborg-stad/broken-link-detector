<?php

namespace BrokenLinkDetector\Config;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use AcfService\Implementations\FakeAcfService;
use BrokenLinkDetector\Config\Config;

class ConfigTest extends TestCase
{
    private FakeWpService $wpService;
    private FakeAcfService $acfService;
    private Config $config;

    protected function setUp(): void
    {
        $this->wpService = new FakeWpService([
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

        $this->acfService = new FakeAcfService([
            'getField' => []
        ]);

        $this->config = new Config(
            $this->wpService,
            $this->acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );
    }

    /**
     * @testdox getDatabaseVersionKey returns correct key
     */
    public function testGetDatabaseVersionKey(): void
    {
        $result = $this->config->getDatabaseVersionKey();
        $this->assertEquals('broken_link_detector_db_version', $result);
    }

    /**
     * @testdox getDatabaseVersion returns correct version
     */
    public function testGetDatabaseVersion(): void
    {
        $result = $this->config->getDatabaseVersion();
        $this->assertEquals('2.0.0', $result);
    }

    /**
     * @testdox getTableName returns correct table name
     */
    public function testGetTableName(): void
    {
        $result = $this->config->getTableName();
        $this->assertEquals('broken_links_detector', $result);
    }

    /**
     * @testdox getPluginUrl returns correct plugin URL
     */
    public function testGetPluginUrl(): void
    {
        $result = $this->config->getPluginUrl();
        $this->assertEquals('https://example.com/plugin', $result);
    }

    /**
     * @testdox getPluginPath returns correct plugin path
     */
    public function testGetPluginPath(): void
    {
        $result = $this->config->getPluginPath();
        $this->assertEquals('/path/to/plugin', $result);
    }

    /**
     * @testdox getPluginFieldsPath returns correct fields path
     */
    public function testGetPluginFieldsPath(): void
    {
        $result = $this->config->getPluginFieldsPath();
        $this->assertEquals('/path/to/pluginsource/fields', $result);
    }

    /**
     * @testdox getTextDomain returns correct text domain
     */
    public function testGetTextDomain(): void
    {
        $result = $this->config->getTextDomain();
        $this->assertEquals('broken-link-detector', $result);
    }

    /**
     * @testdox linkUpdaterBannedPostTypes returns correct banned post types
     */
    public function testLinkUpdaterBannedPostTypes(): void
    {
        $result = $this->config->linkUpdaterBannedPostTypes();
        $expected = ['attachment', 'revision', 'acf', 'acf-field', 'acf-field-group'];
        $this->assertEquals($expected, $result);
    }

    /**
     * @testdox linkDetectBannedPostTypes returns correct banned post types
     */
    public function testLinkDetectBannedPostTypes(): void
    {
        $result = $this->config->linkDetectBannedPostTypes();
        $expected = ['attachment', 'revision', 'acf', 'acf-field', 'acf-field-group'];
        $this->assertEquals($expected, $result);
    }

    /**
     * @testdox linkDetectAllowedPostStatuses returns correct allowed post statuses
     */
    public function testLinkDetectAllowedPostStatuses(): void
    {
        $result = $this->config->linkDetectAllowedPostStatuses();
        $expected = ['publish', 'private', 'password'];
        $this->assertEquals($expected, $result);
    }

    /**
     * @testdox responseCodesConsideredBroken returns correct response codes
     */
    public function testResponseCodesConsideredBroken(): void
    {
        $result = $this->config->responseCodesConsideredBroken();
        $expected = [400, 403, 404, 410, 500, 502, 503, 504];
        $this->assertEquals($expected, $result);
    }

    /**
     * @testdox checkIfDnsRespondsBeforeProbingUrl returns correct boolean
     */
    public function testCheckIfDnsRespondsBeforeProbingUrl(): void
    {
        $result = $this->config->checkIfDnsRespondsBeforeProbingUrl();
        $this->assertTrue($result);
    }

    /**
     * @testdox getMaxRedirects returns correct number
     */
    public function testGetMaxRedirects(): void
    {
        $result = $this->config->getMaxRedirects();
        $this->assertEquals(5, $result);
    }

    /**
     * @testdox getTimeout returns correct timeout
     */
    public function testGetTimeout(): void
    {
        $result = $this->config->getTimeout();
        $this->assertEquals(5, $result);
    }

    /**
     * @testdox getRecheckInterval returns correct interval
     */
    public function testGetRecheckInterval(): void
    {
        $result = $this->config->getRecheckInterval();
        $this->assertEquals(720, $result); // 60 * 12
    }

    /**
     * @testdox getDomainsThatShouldNotBeChecked returns domains from ACF
     */
    public function testGetDomainsThatShouldNotBeChecked(): void
    {
        $acfService = new FakeAcfService([
            'getField' => [
                'broken_links_local_domains' => [
                    ['domain' => 'https://example.com'],
                    ['domain' => 'https://test.com']
                ]
            ]
        ]);

        $config = new Config(
            $this->wpService,
            $acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $result = $config->getDomainsThatShouldNotBeChecked();
        $expected = ['example.com', 'test.com'];
        $this->assertEquals($expected, $result);
    }

    /**
     * @testdox isContextCheckEnabled returns false when disabled
     */
    public function testIsContextCheckEnabledWhenDisabled(): void
    {
        $result = $this->config->isContextCheckEnabled();
        $this->assertFalse($result);
    }

    /**
     * @testdox isContextCheckEnabled returns true when enabled and URL provided
     */
    public function testIsContextCheckEnabledWhenEnabled(): void
    {
        $acfService = new FakeAcfService([
            'getField' => [
                'broken_links_context_check_enabled' => true,
                'broken_links_context_check_url' => 'https://example.com/check'
            ]
        ]);

        $config = new Config(
            $this->wpService,
            $acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $result = $config->isContextCheckEnabled();
        $this->assertTrue($result);
    }

    /**
     * @testdox getContextCheckUrl returns correct URL
     */
    public function testGetContextCheckUrl(): void
    {
        $acfService = new FakeAcfService([
            'getField' => [
                'broken_links_context_check_url' => 'https://example.com/check'
            ]
        ]);

        $config = new Config(
            $this->wpService,
            $acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $result = $config->getContextCheckUrl();
        $this->assertEquals('https://example.com/check', $result);
    }

    /**
     * @testdox getContextCheckTimeout returns correct timeout
     */
    public function testGetContextCheckTimeout(): void
    {
        $result = $this->config->getContextCheckTimeout();
        $this->assertEquals(3000, $result);
    }

    /**
     * @testdox getContextCheckSuccessClass returns correct class
     */
    public function testGetContextCheckSuccessClass(): void
    {
        $result = $this->config->getContextCheckSuccessClass();
        $this->assertEquals('context-check-avabile', $result);
    }

    /**
     * @testdox getContextCheckFailedClass returns correct class
     */
    public function testGetContextCheckFailedClass(): void
    {
        $result = $this->config->getContextCheckFailedClass();
        $this->assertEquals('context-check-unavailable', $result);
    }

    /**
     * @testdox getContextCheckIsToolTipActive returns correct status
     */
    public function testGetContextCheckIsToolTipActive(): void
    {
        $acfService = new FakeAcfService([
            'getField' => [
                'broken_links_context_notify_by' => ['tooltip']
            ]
        ]);

        $config = new Config(
            $this->wpService,
            $acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $result = $config->getContextCheckIsToolTipActive();
        $this->assertTrue($result);
    }

    /**
     * @testdox getContextCheckTooltipText returns correct text
     */
    public function testGetContextCheckTooltipText(): void
    {
        $result = $this->config->getContextCheckTooltipText();
        $this->assertEquals('Link unavailable', $result);
    }

    /**
     * @testdox getContextCheckIsModalActive returns correct status
     */
    public function testGetContextCheckIsModalActive(): void
    {
        $acfService = new FakeAcfService([
            'getField' => [
                'broken_links_context_notify_by' => ['modal']
            ]
        ]);

        $config = new Config(
            $this->wpService,
            $acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $result = $config->getContextCheckIsModalActive();
        $this->assertTrue($result);
    }

    /**
     * @testdox getContextCheckModalTitle returns correct title
     */
    public function testGetContextCheckModalTitle(): void
    {
        $result = $this->config->getContextCheckModalTitle();
        $this->assertEquals('Content unavailable', $result);
    }

    /**
     * @testdox getContextCheckModalContent returns correct content
     */
    public function testGetContextCheckModalContent(): void
    {
        $result = $this->config->getContextCheckModalContent();
        $this->assertStringContainsString('This link cannot be accessed', $result);
    }

    /**
     * @testdox getCommandNamespace returns correct namespace
     */
    public function testGetCommandNamespace(): void
    {
        $result = $this->config->getCommandNamespace();
        $this->assertEquals('broken-link-detector', $result);
    }

    /**
     * @testdox getTemplateDirectory returns correct directory
     */
    public function testGetTemplateDirectory(): void
    {
        $result = $this->config->getTemplateDirectory();
        $this->assertEquals('/path/to/pluginsource/views', $result);
    }

    /**
     * @testdox createFilterKey creates correct filter key
     */
    public function testCreateFilterKey(): void
    {
        $result = $this->config->createFilterKey('testFilter');
        $this->assertEquals('BrokenLinkDetector/Config/TestFilter', $result);
    }

    /**
     * @testdox Filters are properly applied through wpService
     */
    public function testFiltersAreApplied(): void
    {
        $wpService = new FakeWpService([
            'applyFilters' => function($filter, $value) {
                if ($filter === 'BrokenLinkDetector/Config/GetDatabaseVersionKey') {
                    return 'custom_db_version_key';
                }
                return $value;
            }
        ]);

        $config = new Config(
            $wpService,
            $this->acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $result = $config->getDatabaseVersionKey();
        $this->assertEquals('custom_db_version_key', $result);
    }
}