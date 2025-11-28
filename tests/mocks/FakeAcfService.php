<?php

namespace AcfService\Implementations;

use AcfService\Contracts\GetField;
use AcfService\Contracts\AddOptionsPage;

/**
 * Mock FakeAcfService for testing purposes
 */
class FakeAcfService implements GetField, AddOptionsPage
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

    public function getField($field, $post_id = false)
    {
        if (isset($this->methods['getField'][$field])) {
            return $this->methods['getField'][$field];
        }
        return $this->methods['getField'] ?? null;
    }

    public function addOptionsPage($args)
    {
        return $this->__call('addOptionsPage', [$args]);
    }
}