<?php

declare(strict_types=1);

namespace BrokenLinkDetector\Admin\Settings;

use AcfService\Contracts\AddOptionsPage;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;

class SettingsPage implements Hookable
{
    /**
     * @param iterable<Hookable> $filters
     */
    public function __construct(
        private AddAction&__ $wpService,
        private AddOptionsPage $acfService,
        private iterable $additionalHooks, // Inject iterable of Hookable objects
    ) {}

    /**
     * Add hooks
     *
     * @return void
     */
    public function addHooks(): void
    {
        $this->wpService->addAction('acf/init', [$this, 'registerSettingsPage']);
        $this->registerAdditionalHooks();
    }

    /**
     * Register additional hooks provided in $additionalHooks
     *
     * @return void
     */
    private function registerAdditionalHooks(): void
    {
        foreach ($this->additionalHooks as $hook) {
            if (!$hook instanceof Hookable) {
                throw new \InvalidArgumentException(
                    sprintf('Expected instance of Hookable, got %s', get_debug_type($hook)),
                );
            }
            $hook->addHooks();
        }
    }

    /**
     * Register the settings page
     *
     * @return void
     */
    public function registerSettingsPage(): void
    {
        $this->acfService->addOptionsPage(array(
            'menu_slug' => 'broken-links-settings',
            'page_title' => $this->wpService->__('Broken Links Settings', 'broken-link-detector'),
            'active' => true,
            'menu_title' => $this->wpService->__('Broken Links Settings', 'broken-link-detector'),
            'capability' => 'administrator',
            'parent_slug' => 'options-general.php',
            'position' => '',
            'icon_url' => '',
            'redirect' => true,
            'post_id' => 'options',
            'autoload' => true,
            'update_button' => $this->wpService->__('Update', 'broken-link-detector'),
            'updated_message' => $this->wpService->__('Settings updated', 'broken-link-detector'),
        ));
    }
}
