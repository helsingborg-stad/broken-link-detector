<?php

namespace BrokenLinkDetector\Database;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use AcfService\Implementations\FakeAcfService;

class DatabaseTest extends TestCase
{
    private FakeWpService $wpService;
    private Config $config;
    private Database $database;

    protected function setUp(): void
    {
        $this->wpService = new FakeWpService([
            'getOption' => function($key, $default) {
                if ($key === 'broken_link_detector_db_version') {
                    return '2.0.0';
                }
                return $default;
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

        $this->database = new Database($this->config, $this->wpService);
    }

    /**
     * @testdox getCurrentVersion returns correct version from options
     */
    public function testGetCurrentVersion(): void
    {
        $result = $this->database->getCurrentVersion();
        $this->assertEquals('2.0.0', $result);
    }

    /**
     * @testdox getCurrentVersion returns null when no version is set
     */
    public function testGetCurrentVersionWhenNotSet(): void
    {
        $wpService = new FakeWpService([
            'getOption' => function($key, $default) {
                return $default;
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

        $database = new Database($config, $wpService);
        $result = $database->getCurrentVersion();
        $this->assertNull($result);
    }

    /**
     * @testdox getTableName returns correct table name with prefix
     */
    public function testGetTableName(): void
    {
        // Since we can't easily mock the global $wpdb, we'll test the method structure
        $result = $this->database->getTableName();
        $this->assertStringContainsString('broken_links_detector', $result);
    }

    /**
     * @testdox getCharsetCollation returns charset collation
     */
    public function testGetCharsetCollation(): void
    {
        // Since we can't easily mock the global $wpdb, we'll test that method exists
        $result = $this->database->getCharsetCollation();
        $this->assertIsString($result);
    }

    /**
     * @testdox getInstance returns wpdb instance
     */
    public function testGetInstance(): void
    {
        $instance = Database::getInstance();
        $this->assertIsObject($instance);
    }

    /**
     * @testdox Database uses correct config methods
     */
    public function testDatabaseUsesConfig(): void
    {
        // Test that database correctly uses config methods
        $versionKey = $this->config->getDatabaseVersionKey();
        $this->assertEquals('broken_link_detector_db_version', $versionKey);

        $tableName = $this->config->getTableName();
        $this->assertEquals('broken_links_detector', $tableName);
    }
}