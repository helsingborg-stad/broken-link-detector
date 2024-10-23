<?php

namespace BrokenLinkDetector\HooksRegistrar;

use BrokenLinkDetector\HooksRegistrar\Hookable;

interface HooksRegistrarInterface
{
    public function register(Hookable $object): HooksRegistrarInterface;
}