<?php

namespace BrokenLinkDetector\Asset;

use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\WpEnqueueScript;
use WpService\Contracts\WpEnqueueStyle;
use WpService\Contracts\WpLocalizeScript;
use WpService\Contracts\WpRegisterScript;
use WpService\Contracts\WpRegisterStyle;

abstract class AssetRegistry implements Hookable, AssetInterface
{
    protected Config $config;

    abstract public function getFilename(): string;
    abstract public function getHandle(): string;
    abstract public function getLocalizeData(): ?array;

    public function __construct(private AddAction&WpRegisterScript&WpRegisterStyle&WpLocalizeScript&WpEnqueueScript&WpEnqueueStyle $wpService, Config $config)
    {
        $this->config = $config;
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('wp_enqueue_scripts', [$this, 'register'], 10);
        $this->wpService->addAction('wp_enqueue_scripts', [$this, 'enqueue'], 20);
    }

    private function getType($filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array($extension, ["css", "js"])) {
            return $extension;
        }

        throw new \Exception('Invalid type enqueued in Enqueue class. Must be either ".js" or ".css"');
    }

    /**
     * Register the script or style
     *
     * @return void
     * @thows \Exception
     */
    public function register(): void
    {
        $filename = $this->getFilename();

        if ($this->getType($filename) === 'js') {
            $this->wpService->wpRegisterScript(
                $this->getHandle(),
                $this->getFilename()
            );

            if (!empty($this->getLocalizeData())) {
              $this->wpService->wpLocalizeScript(
                  $this->getHandle(),
                  $this->getHandle() . 'Data',
                  $this->getLocalizeData()
              );
            }
        }

        if ($this->getType($filename) === 'css') {
            $this->wpService->wpRegisterStyle(
                $this->getHandle(),
                $this->getFilename()
            );

            if($this->getLocalizeData() !== null) {
              throw new \Exception('Localize data is not supported for styles');
            }
        }
    }

    /**
     * Enqueue the script or style
     *
     * @return void
     */

    public function enqueue(): void
    {
        $filename = $this->getFilename();

        if ($this->getType($filename) === 'css') {
            $this->wpService->wpEnqueueStyle($this->getHandle());
        }
        if ($this->getType($filename) === 'js') {
            $this->wpService->wpEnqueueScript($this->getHandle());
        }
    }
}