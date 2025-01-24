<?php

namespace BrokenLinkDetector\Asset;

use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\BrokenLinkRegistry\Registry\ManageRegistry;
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
    protected ?ManageRegistry $registry = null;

    abstract public function getFilename(): string;
    abstract public function getHandle(): string;
    abstract public function getDependencies(): array;
    abstract public function getLocalizeData(): ?array;
    abstract public function getHook(): string;

    public function __construct(
        private AddAction&WpRegisterScript&WpRegisterStyle&WpLocalizeScript&WpEnqueueScript&WpEnqueueStyle $wpService, 
        Config $config, 
        ?ManageRegistry $registry = null
    )
    {
        $this->config = $config;

        if (!is_null($registry)) {
            $this->registry = $registry;
        }
    }

    public function addHooks(): void
    {
        if(!in_array($this->getHook(), ['wp_enqueue_scripts', 'admin_enqueue_scripts', 'login_enqueue_scripts', 'mce_external_plugins'])) {
            throw new \Exception('Invalid hook enqueued in Enqueue class. Must be either "mce_external_plugins", "wp_enqueue_scripts", "admin_enqueue_scripts" or "login_enqueue_scripts"');
        }
        $this->wpService->addAction($this->getHook(), [$this, 'register'], 10);
        $this->wpService->addAction($this->getHook(), [$this, 'enqueue'], 20);
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
                $this->getFilename(),
                $this->getDependencies(),
                false,
                ['in_footer' => true]
            );

            if (!empty($this->getLocalizeData())) {
              $this->wpService->wpLocalizeScript(
                  $this->getHandle(),
                  $this->camelCaseObjectName($this->getHandle() . '-data'),
                  $this->getLocalizeData()
              );
            }
        }

        if ($this->getType($filename) === 'css') {
            $this->wpService->wpRegisterStyle(
                $this->getHandle(),
                $this->getFilename(),
                $this->getDependencies(),
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
        if($this->isEnabled() === false) {
            return;
        }

        $filename = $this->getFilename();

        if ($this->getType($filename) === 'css') {
            $this->wpService->wpEnqueueStyle($this->getHandle());
        }
        if ($this->getType($filename) === 'js') {
            $this->wpService->wpEnqueueScript(
                $this->getHandle()
        );
        }
    }

    /**
     * Get the object name in camel case
     *
     * @param string $objectName
     * @return string
     */
    private function camelCaseObjectName($objectName): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $objectName))));
    }
}