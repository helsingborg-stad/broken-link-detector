<?php

namespace BrokenLinkDetector\Admin\Settings;

use BrokenLinkDetector\HooksRegistrar\Hookable;
use AcfService\Contracts\AddOptionsPage;
use WpService\Contracts\AddAction;
use WpService\Contracts\__;

class SettingsPage implements Hookable
{
    public function __construct(private AddAction&__ $wpService, private AddOptionsPage $acfService){}

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/init', [$this, 'registerSettingsPage']);
    }

    public function registerSettingsPage(): void
    {
        $this->acfService->addOptionsPage(array(
            'menu_slug'       => 'broken-links-settings',
            'page_title'      => $this->wpService->__('Broken Links Settings', 'broken-link-detector'),
            'active'          => true,
            'menu_title'      => $this->wpService->__('Broken Links Settings', 'broken-link-detector'),
            'capability'      => 'administrator',
            'parent_slug'     => 'options-general.php',
            'position'        => '',
            'icon_url'        => '',
            'redirect'        => true,
            'post_id'         => 'options',
            'autoload'        => false,
            'update_button'   => $this->wpService->__('Update', 'broken-link-detector'),
            'updated_message' => $this->wpService->__('Settings updated', 'broken-link-detector'),
        ));
    }
}