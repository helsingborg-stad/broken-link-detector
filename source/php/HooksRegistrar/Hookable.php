<?php

declare(strict_types=1);

namespace BrokenLinkDetector\HooksRegistrar;

interface Hookable
{
    public function addHooks(): void;
}
