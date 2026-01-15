<?php

declare(strict_types=1);

namespace BrokenLinkDetector\HooksRegistrar;

use BrokenLinkDetector\HooksRegistrar\Hookable;

class HooksRegistrar implements HooksRegistrarInterface
{
    public function register(Hookable $object): HooksRegistrarInterface
    {
        $object->addHooks();

        return $this;
    }
}
