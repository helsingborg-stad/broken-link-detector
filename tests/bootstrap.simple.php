<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load mock contracts first
require_once __DIR__ . '/mocks/WpServiceContracts.php';
require_once __DIR__ . '/mocks/AcfServiceContracts.php';

// Load mock classes
require_once __DIR__ . '/mocks/FakeWpService.php';
require_once __DIR__ . '/mocks/FakeAcfService.php';

// Create namespace aliases for missing classes
if (!class_exists('WpService\WpService')) {
    class_alias('WpService\Implementations\FakeWpService', 'WpService\WpService');
}

if (!class_exists('AcfService\AcfService')) {
    class_alias('AcfService\Implementations\FakeAcfService', 'AcfService\AcfService');
}

// Define constants that WordPress would normally define
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', '/wp-content/plugins');
}

// Mock WordPress functions that might be needed
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return htmlspecialchars($text);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text);
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return $url;
    }
}

if (!function_exists('get_permalink')) {
    function get_permalink($id = 0) {
        return 'https://example.com/post/' . $id;
    }
}

if (!function_exists('get_the_title')) {
    function get_the_title($id = 0) {
        return 'Post Title ' . $id;
    }
}

if (!function_exists('selected')) {
    function selected($selected, $current, $echo = true) {
        return $selected === $current ? ' selected="selected"' : '';
    }
}

if (!function_exists('submit_button')) {
    function submit_button($text, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null) {
        echo '<input type="submit" value="' . esc_html($text) . '" class="button-' . esc_attr($type) . '">';
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sanitize_sql_orderby')) {
    function sanitize_sql_orderby($orderby) {
        return preg_replace('/[^a-zA-Z0-9_,\s]/', '', $orderby);
    }
}

if (!function_exists('absint')) {
    function absint($value) {
        return abs((int) $value);
    }
}

// Mock WP_List_Table class
if (!class_exists('WP_List_Table')) {
    class WP_List_Table {
        protected $items = [];
        
        public function __construct($args = []) {
            // Mock constructor
        }
        
        public function prepare_items() {
            // Mock method
        }
        
        public function display() {
            // Mock method
        }
        
        public function get_columns() {
            return [];
        }
        
        public function get_sortable_columns() {
            return [];
        }
        
        protected function column_default($item, $column_name) {
            return '';
        }
        
        protected function extra_tablenav($which) {
            // Mock method
        }
    }
}

// Mock global $wpdb
if (!isset($GLOBALS['wpdb'])) {
    $GLOBALS['wpdb'] = new class {
        public $prefix = 'wp_';
        
        public function get_charset_collate() {
            return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }
    };
}

// Create wpdb class
if (!class_exists('wpdb')) {
    class wpdb {
        public $prefix = 'wp_';
        
        public function get_charset_collate() {
            return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }
    }
    $GLOBALS['wpdb'] = new wpdb();
}