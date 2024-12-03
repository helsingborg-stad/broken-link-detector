<?php
namespace BrokenLinkDetector;

use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\WpService;
use BrokenLinkDetector\Config\Config;
use Throwable;
use ComponentLibrary\Init;

class Modal implements Hookable {

  public function __construct(private WpService $wpService, private Config $config, private \ComponentLibrary\Init $componentLibrary) {}

  public function addHooks(): void 
  {
    $this->wpService->addAction('wp_footer', [$this, 'renderView']);
  }

  /**
   * Render the modal view
   */
  public function renderView(): string
  {
    $data = [
      'title' => $this->config->getModalTitle(),
      'content' => $this->config->getModalContent(),
      'ctaLink' => '{{BLD_CTA_LINK}}',
      'ctaLabel' => $this->wpService->__("Open Anyway", 'broken-link-detector'),
      'close' => $this->wpService->__("Close", 'broken-link-detector'),
    ];

    $blade = $this->componentLibrary->getEngine();

    try {
        echo $blade->makeView('modal', $data, [], [])->render();
    } catch (Throwable $e) {
        $blade->errorHandler($e)->print();
    }
    return false; 
  }
}