<?php

namespace BrokenLinkDetector\HooksRegistrar;

interface Hookable
{
    public function addHooks(): void;
}