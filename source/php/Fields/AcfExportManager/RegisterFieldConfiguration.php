<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Fields\AcfExportManager;

use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;

class RegisterFieldConfiguration implements Hookable
{
    public function __construct(
        private AddAction $wpService,
        private string $fieldConfigurationDirectory,
    ) {
        if (empty($this->fieldConfigurationDirectory)) {
            throw new \InvalidArgumentException('Field configuration directory is required');
        }
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/init', array($this, 'initFieldRegistration'));
    }

    public function initFieldRegistration(): void
    {
        $acfExportManager = new \AcfExportManager\AcfExportManager();
        $acfExportManager->setTextdomain('api-event-manager');
        $acfExportManager->setExportFolder($this->fieldConfigurationDirectory ?? null);
        $acfExportManager->autoExport(array(
            'local-domains' => 'group_6718e7ca78c94',
            'context-detection' => 'group_6718e9e8554ca',
        ));

        $acfExportManager->import();
    }
}
