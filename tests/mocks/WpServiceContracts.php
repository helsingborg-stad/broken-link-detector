<?php

namespace WpService\Contracts;

interface AddAction {
    public function addAction($hook, $callback);
}

interface AddManagementPage {
    public function addManagementPage($title, $menuTitle, $capability, $menuSlug, $callback);
}

interface ApplyFilters {
    public function applyFilters($filter, $value);
}

interface __ {
    public function __($text, $domain = 'default');
}

interface LoadPluginTextDomain {
    public function loadPluginTextdomain($domain, $deprecated, $plugin_rel_path);
}

interface GetOption {
    public function getOption($key, $default = null);
}