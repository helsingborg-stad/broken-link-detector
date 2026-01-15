<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Asset;

use BrokenLinkDetector\Asset\AssetRegistry;

class ContextDetection extends AssetRegistry implements AssetInterface
{
    public function shouldEnqueue(): bool
    {
        return $this->config->isContextCheckEnabled();
    }

    public function getHook(): string
    {
        return 'wp_enqueue_scripts';
    }

    public function getHandle(): string
    {
        return 'broken-link-context-detection';
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function getFilename(): string
    {
        return 'js/context-detector.js';
    }

    public function getLocalizeData(): ?array
    {
        return [
            'isEnabled' => $this->config->isContextCheckEnabled(),
            'checkUrl' => $this->config->getContextCheckUrl(),
            'checkTimeout' => $this->config->getContextCheckTimeout(),
            'domains' => $this->config->getContextCheckDomainsToDisable(),
            'successClass' => $this->config->getContextCheckSuccessClass(),
            'failedClass' => $this->config->getContextCheckFailedClass(),
            'isToolTipActive' => $this->config->getContextCheckIsToolTipActive(),
            'tooltip' => $this->config->getContextCheckTooltipText(),
            'isModalActive' => $this->config->getContextCheckIsModalActive(),
        ];
    }
}
