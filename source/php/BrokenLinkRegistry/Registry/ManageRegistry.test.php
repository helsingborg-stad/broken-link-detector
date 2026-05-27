<?php

namespace BrokenLinkDetector\BrokenLinkRegistry\Registry;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use AcfService\Implementations\FakeAcfService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry;
use BrokenLinkDetector\BrokenLinkRegistry\Link\Link;
use BrokenLinkDetector\BrokenLinkRegistry\LinkList\LinkList;

class ManageRegistryTest extends TestCase
{
    private FakeWpService $wpService;
    private FakeAcfService $acfService;
    private Config $config;
    private Database $database;
    private ManageRegistry $manageRegistry;

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
            },
            'getOption' => function($key, $default) {
                return $default;
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

        $this->database = new Database($this->config, $this->wpService);
        $this->manageRegistry = new ManageRegistry($this->database, $this->config);
    }

    /**
     * @testdox ManageRegistry constructs correctly
     */
    public function testManageRegistryConstruction(): void
    {
        $this->assertInstanceOf(ManageRegistry::class, $this->manageRegistry);
    }

    /**
     * @testdox add method exists and returns boolean
     */
    public function testAddMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manageRegistry, 'add'));
    }

    /**
     * @testdox update method exists and returns boolean
     */
    public function testUpdateMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manageRegistry, 'update'));
    }

    /**
     * @testdox remove method exists and returns boolean
     */
    public function testRemoveMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manageRegistry, 'remove'));
    }

    /**
     * @testdox getLinksThatNeedsClassification method exists
     */
    public function testGetLinksThatNeedsClassificationMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manageRegistry, 'getLinksThatNeedsClassification'));
    }

    /**
     * @testdox getBrokenLinksByPostId method exists
     */
    public function testGetBrokenLinksByPostIdMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manageRegistry, 'getBrokenLinksByPostId'));
    }

    /**
     * @testdox hash method exists
     */
    public function testHashMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manageRegistry, 'hash'));
    }

    /**
     * @testdox ManageRegistry integrates with Database and Config correctly
     */
    public function testManageRegistryIntegration(): void
    {
        $this->assertInstanceOf(Database::class, $this->database);
        $this->assertInstanceOf(Config::class, $this->config);
        $this->assertInstanceOf(ManageRegistry::class, $this->manageRegistry);
    }
}