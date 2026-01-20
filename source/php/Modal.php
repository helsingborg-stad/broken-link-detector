<?php

declare(strict_types=1);

namespace BrokenLinkDetector;

use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use ComponentLibrary\Init;
use Throwable;
use WpService\WpService;

class Modal implements Hookable
{
    public function __construct(
        private WpService $wpService,
        private Config $config,
        private \ComponentLibrary\Init $componentLibrary,
    ) {}

    public function addHooks(): void
    {
        $this->wpService->addAction('wp_footer', [$this, 'renderView']);
    }

    /**
     * Render the modal view
     */
    public function renderView(): bool
    {
        if ($this->config->getContextCheckIsModalActive() === false) {
            return false;
        }

        $data = [
            'title' => $this->config->getContextCheckModalTitle(),
            'content' => $this->config->getContextCheckModalContent(),
            'ctaLink' => '{{BLD_CTA_LINK}}',
            'ctaLabel' => $this->wpService->__('Open Anyway', 'broken-link-detector'),
            'close' => $this->wpService->__('Close', 'broken-link-detector'),
        ];

        $blade = $this->componentLibrary->getEngine();

        try {
            echo $blade->makeView('modal', $data, [], [])->render();
            return true;
        } catch (Throwable $e) {
            $blade->errorHandler($e)->print();
        }
        return false;
    }
}
