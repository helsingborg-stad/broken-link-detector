<?php

namespace BrokenLinkDetector;

use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\LoadPluginTextDomain;

class TextDomain implements Hookable
{
    public function __construct(private string $textDomain, private AddAction&LoadPluginTextDomain $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('plugins_loaded', array($this, 'loadTextDomain'));
    }

    public function loadTextDomain(): void
    {
        $relativeTo = defined('BROKENLINKDETECTOR_PATH') ? constant('BROKENLINKDETECTOR_PATH') . 'languages/' : '';
        $this->wpService->loadPluginTextDomain($this->textDomain, false, $relativeTo);
    }
}