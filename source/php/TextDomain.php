<?php

namespace BrokenLinkDetector;

use BrokenLinkDetector\Config\ConfigInterface;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\LoadPluginTextDomain;

class TextDomain implements TextDomainInterface, Hookable
{
    public function __construct(private AddAction&LoadPluginTextDomain $wpService, private ConfigInterface $config)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('plugins_loaded', array($this, 'loadTextDomain'));
    }

    public function loadTextDomain(): void
    {
        $this->wpService->loadPluginTextDomain(
            $this->config->gettextDomain(), 
            false, 
            $this->config->getPluginPath() . 'languages/'
        );
    }
}