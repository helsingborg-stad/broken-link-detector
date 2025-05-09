<?php

namespace BrokenLinkDetector\HooksRegistrar;

use BrokenLinkDetector\HooksRegistrar\Hookable;
use PHPUnit\Framework\TestCase;

class HooksRegistrarTest extends TestCase
{
    /**
     * @testdox register() calls addHooks() on provided object
     */
    public function testRegisterCallsAddHooksOnProvidedObject()
    {
        $hookable       = $this->getHookableClass();
        $hooksRegistrar = new \BrokenLinkDetector\HooksRegistrar\HooksRegistrar();

        ob_start();
        $hooksRegistrar->register($hookable);

        $this->assertEquals('Hooks added!', ob_get_clean());
    }

    private function getHookableClass(): Hookable
    {
        return new class implements Hookable {
            public function addHooks(): void
            {
                echo 'Hooks added!';
            }
        };
    }
}