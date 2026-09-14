<?php

namespace WpService\Implementations;

use WpService\Contracts\AddAction;
use WpService\Contracts\AddManagementPage;
use WpService\Contracts\ApplyFilters;
use WpService\Contracts\__;
use WpService\Contracts\GetOption;
use WpService\Contracts\LoadPluginTextDomain;

/**
 * Mock FakeWpService for testing purposes
 */
class FakeWpService implements AddAction, AddManagementPage, ApplyFilters, __, GetOption, LoadPluginTextDomain
{
    private array $methods;

    public function __construct(array $methods = [])
    {
        $this->methods = $methods;
    }

    public function __call($method, $args)
    {
        if (isset($this->methods[$method])) {
            if (is_callable($this->methods[$method])) {
                return call_user_func_array($this->methods[$method], $args);
            }
            return $this->methods[$method];
        }
        return null;
    }

    public function addAction($hook, $callback)
    {
        return $this->__call('addAction', [$hook, $callback]);
    }

    public function addManagementPage($title, $menuTitle, $capability, $menuSlug, $callback)
    {
        return $this->__call('addManagementPage', [$title, $menuTitle, $capability, $menuSlug, $callback]);
    }

    public function applyFilters($filter, $value)
    {
        return $this->__call('applyFilters', [$filter, $value]);
    }

    public function __($text, $domain = 'default')
    {
        return $this->__call('__', [$text, $domain]);
    }

    public function getOption($key, $default = null)
    {
        return $this->__call('getOption', [$key, $default]);
    }

    public function getStatusHeaderDesc($code)
    {
        return $this->__call('getStatusHeaderDesc', [$code]);
    }

    public function wpautop($text)
    {
        return $this->__call('wpautop', [$text]);
    }

    public function loadPluginTextdomain($domain, $deprecated, $plugin_rel_path)
    {
        return $this->__call('loadPluginTextdomain', [$domain, $deprecated, $plugin_rel_path]);
    }
}