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

interface GetOption {
    public function getOption($key, $default = null);
}