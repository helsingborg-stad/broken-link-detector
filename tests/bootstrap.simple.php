<?php

// Handle case where autoloader is not available
$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // Create a minimal autoloader for our test classes
    spl_autoload_register(function ($class) {
        // Handle BrokenLinkDetector classes
        $prefix = 'BrokenLinkDetector\\';
        $base_dir = dirname(__DIR__) . '/source/php/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
        
        // Handle PHPUnit classes (basic fallback)
        if (strpos($class, 'PHPUnit\\') === 0) {
            return; // Let PHPUnit handle its own classes
        }
    });
    
    // Create mock contracts for WpService and AcfService
    if (!interface_exists('WpService\Contracts\ApplyFilters')) {
        interface WpService_Contracts_ApplyFilters {
            public function applyFilters($filter, $value);
        }
        class_alias('WpService_Contracts_ApplyFilters', 'WpService\Contracts\ApplyFilters');
    }
    
    if (!interface_exists('WpService\Contracts\__')) {
        interface WpService_Contracts__ {
            public function __($text, $domain = 'default');
        }
        class_alias('WpService_Contracts__', 'WpService\Contracts\__');
    }
    
    if (!interface_exists('WpService\Contracts\AddAction')) {
        interface WpService_Contracts_AddAction {
            public function addAction($hook, $callback);
        }
        class_alias('WpService_Contracts_AddAction', 'WpService\Contracts\AddAction');
    }
    
    if (!interface_exists('WpService\Contracts\AddManagementPage')) {
        interface WpService_Contracts_AddManagementPage {
            public function addManagementPage($title, $menuTitle, $capability, $menuSlug, $callback);
        }
        class_alias('WpService_Contracts_AddManagementPage', 'WpService\Contracts\AddManagementPage');
    }
    
    if (!interface_exists('WpService\Contracts\GetOption')) {
        interface WpService_Contracts_GetOption {
            public function getOption($key, $default = null);
        }
        class_alias('WpService_Contracts_GetOption', 'WpService\Contracts\GetOption');
    }
    
    if (!interface_exists('WpService\Contracts\LoadPluginTextDomain')) {
        interface WpService_Contracts_LoadPluginTextDomain {
            public function loadPluginTextdomain($domain, $deprecated, $plugin_rel_path);
        }
        class_alias('WpService_Contracts_LoadPluginTextDomain', 'WpService\Contracts\LoadPluginTextDomain');
    }
    
    if (!interface_exists('WpService\Contracts\WpService')) {
        interface WpService_Contracts_WpService {
            public function getStatusHeaderDesc($code);
            public function applyFilters($filter, $value);
            public function wpautop($text);
        }
        class_alias('WpService_Contracts_WpService', 'WpService\Contracts\WpService');
    }
    
    if (!interface_exists('AcfService\Contracts\AddOptionsPage')) {
        interface AcfService_Contracts_AddOptionsPage {
            public function addOptionsPage($args);
        }
        class_alias('AcfService_Contracts_AddOptionsPage', 'AcfService\Contracts\AddOptionsPage');
    }
    
    if (!interface_exists('AcfService\Contracts\GetField')) {
        interface AcfService_Contracts_GetField {
            public function getField($field, $post_id = false);
        }
        class_alias('AcfService_Contracts_GetField', 'AcfService\Contracts\GetField');
    }
    
    if (!interface_exists('AcfService\Contracts\AcfService')) {
        interface AcfService_Contracts_AcfService {
            public function getField($field, $post_id = false);
            public function addOptionsPage($args);
        }
        class_alias('AcfService_Contracts_AcfService', 'AcfService\Contracts\AcfService');
    }
    
    // Create mock implementations
    if (!class_exists('WpService\Implementations\FakeWpService')) {
        class WpService_Implementations_FakeWpService implements WpService_Contracts_ApplyFilters, WpService_Contracts__, WpService_Contracts_AddAction, WpService_Contracts_AddManagementPage, WpService_Contracts_GetOption, WpService_Contracts_LoadPluginTextDomain {
            private $mocks = [];
            
            public function __construct($mocks = []) {
                $this->mocks = $mocks;
            }
            
            public function __call($method, $args) {
                if (isset($this->mocks[$method])) {
                    if (is_callable($this->mocks[$method])) {
                        return call_user_func_array($this->mocks[$method], $args);
                    }
                    return $this->mocks[$method];
                }
                return null;
            }
            
            public function addAction($hook, $callback) {
                return $this->__call('addAction', [$hook, $callback]);
            }

            public function addManagementPage($title, $menuTitle, $capability, $menuSlug, $callback) {
                return $this->__call('addManagementPage', [$title, $menuTitle, $capability, $menuSlug, $callback]);
            }

            public function applyFilters($filter, $value) {
                return $this->__call('applyFilters', [$filter, $value]);
            }

            public function __($text, $domain = 'default') {
                return $this->__call('__', [$text, $domain]);
            }

            public function getOption($key, $default = null) {
                return $this->__call('getOption', [$key, $default]);
            }

            public function getStatusHeaderDesc($code) {
                return $this->__call('getStatusHeaderDesc', [$code]);
            }

            public function wpautop($text) {
                return $this->__call('wpautop', [$text]);
            }

            public function loadPluginTextdomain($domain, $deprecated, $plugin_rel_path) {
                return $this->__call('loadPluginTextdomain', [$domain, $deprecated, $plugin_rel_path]);
            }
        }
        class_alias('WpService_Implementations_FakeWpService', 'WpService\Implementations\FakeWpService');
    }
    
    if (!class_exists('AcfService\Implementations\FakeAcfService')) {
        class AcfService_Implementations_FakeAcfService implements AcfService_Contracts_GetField, AcfService_Contracts_AddOptionsPage {
            private $mocks = [];
            
            public function __construct($mocks = []) {
                $this->mocks = $mocks;
            }
            
            public function getField($field, $post_id = false) {
                if (isset($this->mocks['getField'][$field])) {
                    return $this->mocks['getField'][$field];
                }
                return $this->mocks['getField'] ?? null;
            }
            
            public function addOptionsPage($args) {
                return isset($this->mocks['addOptionsPage']) ? 
                    call_user_func($this->mocks['addOptionsPage'], $args) : 
                    null;
            }
        }
        class_alias('AcfService_Implementations_FakeAcfService', 'AcfService\Implementations\FakeAcfService');
    }
}

// Load mock contracts and classes only if autoloader didn't handle them
if (!interface_exists('WpService\Contracts\WpService')) {
    require_once __DIR__ . '/mocks/WpServiceContracts.php';
}
if (!interface_exists('AcfService\Contracts\AcfService')) {
    require_once __DIR__ . '/mocks/AcfServiceContracts.php';
}

if (!class_exists('WpService\Implementations\FakeWpService')) {
    require_once __DIR__ . '/mocks/FakeWpService.php';
}
if (!class_exists('AcfService\Implementations\FakeAcfService')) {
    require_once __DIR__ . '/mocks/FakeAcfService.php';
}

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
        
        public function get_results($query) {
            return [];
        }
        
        public function get_col($query) {
            return [];
        }
        
        public function prepare($query, ...$args) {
            return $query;
        }
        
        public function query($query) {
            return true;
        }
    }
    $GLOBALS['wpdb'] = new wpdb();
}