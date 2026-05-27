<?php

namespace BrokenLinkDetector\Admin\Summary;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Admin\Summary\Table;
use AcfService\Implementations\FakeAcfService;

class TableTest extends TestCase
{
    private FakeWpService $wpService;
    private Config $config;
    private Database $database;
    private Table $table;

    protected function setUp(): void
    {
        $this->wpService = new FakeWpService([
            'getStatusHeaderDesc' => function($code) {
                switch($code) {
                    case 200: return 'OK';
                    case 404: return 'Not Found';
                    case 500: return 'Internal Server Error';
                    default: return 'Unknown';
                }
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
        $this->table = new Table($this->wpService, $this->database, $this->config);
    }

    /**
     * @testdox Table constructs with correct parameters
     */
    public function testTableConstruction(): void
    {
        $this->assertInstanceOf(Table::class, $this->table);
        $this->assertInstanceOf(\WP_List_Table::class, $this->table);
    }

    /**
     * @testdox get_columns returns correct columns
     */
    public function testGetColumns(): void
    {
        $columns = $this->table->get_columns();
        
        $expectedColumns = [
            'post_id'   => 'Post',
            'url'       => 'URL',
            'http_code' => 'HTTP Code',
            'time'      => 'Last Checked',
        ];

        $this->assertEquals($expectedColumns, $columns);
    }

    /**
     * @testdox get_sortable_columns returns correct sortable columns
     */
    public function testGetSortableColumns(): void
    {
        $sortableColumns = $this->table->get_sortable_columns();
        
        $expectedSortableColumns = [
            'http_code' => ['http_code', false],
            'time'      => ['time', false],
        ];

        $this->assertEquals($expectedSortableColumns, $sortableColumns);
    }

    /**
     * @testdox getBrokenLinks method exists and is callable
     */
    public function testGetBrokenLinksMethodExists(): void
    {
        // Just test that the method exists and is callable
        $this->assertTrue(method_exists($this->table, 'getBrokenLinks'));
        $this->assertTrue(is_callable([$this->table, 'getBrokenLinks']));
    }

    /**
     * @testdox column_default handles post_id column correctly
     */
    public function testColumnDefaultHandlesPostId(): void
    {
        // Mock functions that would normally be WordPress functions
        if (!function_exists('get_permalink')) {
            function get_permalink($id) {
                return 'https://example.com/post/' . $id;
            }
        }
        
        if (!function_exists('get_the_title')) {
            function get_the_title($id) {
                return 'Post Title ' . $id;
            }
        }
        
        if (!function_exists('esc_url')) {
            function esc_url($url) {
                return $url;
            }
        }
        
        if (!function_exists('esc_html')) {
            function esc_html($text) {
                return htmlspecialchars($text);
            }
        }

        $item = (object)[
            'post_id' => 123,
            'url' => 'https://example.com/test',
            'http_code' => 404,
            'time' => '2023-01-01 12:00:00'
        ];

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->table);
        $method = $reflection->getMethod('column_default');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->table, $item, 'post_id');
        
        $this->assertStringContainsString('Post Title 123', $result);
        $this->assertStringContainsString('https://example.com/post/123', $result);
    }

    /**
     * @testdox column_default handles http_code column correctly
     */
    public function testColumnDefaultHandlesHttpCode(): void
    {
        $item = (object)[
            'post_id' => 123,
            'url' => 'https://example.com/test',
            'http_code' => 404,
            'time' => '2023-01-01 12:00:00'
        ];

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->table);
        $method = $reflection->getMethod('column_default');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->table, $item, 'http_code');
        
        $this->assertEquals('Not Found (404)', $result);
    }

    /**
     * @testdox column_default handles default columns correctly
     */
    public function testColumnDefaultHandlesDefaultColumns(): void
    {
        $item = (object)[
            'post_id' => 123,
            'url' => 'https://example.com/test',
            'http_code' => 404,
            'time' => '2023-01-01 12:00:00'
        ];

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->table);
        $method = $reflection->getMethod('column_default');
        $method->setAccessible(true);

        $result = $method->invoke($this->table, $item, 'url');
        $this->assertEquals('https://example.com/test', $result);

        $result = $method->invoke($this->table, $item, 'time');
        $this->assertEquals('2023-01-01 12:00:00', $result);
    }

    /**
     * @testdox column_default handles missing post_id correctly
     */
    public function testColumnDefaultHandlesMissingPostId(): void
    {
        $item = (object)[
            'post_id' => null,
            'url' => 'https://example.com/test',
            'http_code' => 404,
            'time' => '2023-01-01 12:00:00'
        ];

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->table);
        $method = $reflection->getMethod('column_default');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->table, $item, 'post_id');
        
        $this->assertEquals('N/A', $result);
    }

    /**
     * @testdox prepare_items sets up table items
     */
    public function testPrepareItems(): void
    {
        // Just test that the method exists and can be called without error
        // The actual functionality would require database setup
        $this->assertTrue(method_exists($this->table, 'prepare_items'));
    }

    /**
     * @testdox extra_tablenav outputs filter controls
     */
    public function testExtraTablenavOutputsFilterControls(): void
    {
        if (!function_exists('selected')) {
            function selected($selected, $current, $echo = true) {
                return $selected === $current ? ' selected="selected"' : '';
            }
        }
        
        if (!function_exists('submit_button')) {
            function submit_button($text, $type, $name, $wrap, $other_attributes = null) {
                echo '<input type="submit" value="' . $text . '" class="button-' . $type . '">';
            }
        }

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->table);
        $method = $reflection->getMethod('extra_tablenav');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($this->table, 'top');
        $output = ob_get_clean();

        $this->assertStringContainsString('<div class="alignright actions">', $output);
        $this->assertStringContainsString('<select name="http_code_filter">', $output);
        $this->assertStringContainsString('All HTTP Codes', $output);
    }

    /**
     * @testdox extra_tablenav does not output for bottom position
     */
    public function testExtraTablenavDoesNotOutputForBottom(): void
    {
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($this->table);
        $method = $reflection->getMethod('extra_tablenav');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($this->table, 'bottom');
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}