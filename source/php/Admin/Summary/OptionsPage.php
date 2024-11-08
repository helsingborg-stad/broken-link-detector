<?php

namespace BrokenLinkDetector\Admin\Summary;

use AcfService\AcfService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\HooksRegistrar\Hookable;
use WpService\Contracts\AddManagementPage;
use WpService\Contracts\AddAction;
use WpService\Contracts\__;
use WpService\WpService;
use WP_List_Table;
use BrokenLinkDetector\Admin\Summary\Table;

class OptionsPage implements Hookable
{
    public function __construct(private AddManagementPage&AddAction&__ $wpService, private Database $db, private Config $config)
    {
        
    }

    public function addHooks(): void
    {
        $this->wpService->addAction('admin_menu', [$this, 'registerSummaryPage']);
    }

    public function registerSummaryPage(): void
    {
        // Add the management page in the WordPress admin
        $this->wpService->addManagementPage(
            $this->wpService->__('Broken Links Report', 'broken-link-detector'),
            $this->wpService->__('Broken Links Report', 'broken-link-detector'),
            'edit_pages',
            'broken-links-report',
            [$this, 'renderSummaryPage']
        );
    }

    public function renderSummaryPage(): void
    {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html__('Broken Links Report', 'broken-link-detector') . '</h1>';
        echo '<p class="description">' . esc_html__('Here is a summary of broken links found in your content.', 'broken-link-detector') . '</p>';
        
        echo '<hr class="wp-header-end">';
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="broken-links-report">';

        // Initialize and display the table
        $table = new Table($this->db, $this->config);
        $table->prepare_items();
        $table->display();
        
        echo '</form>';
        echo '</div>';
    }
}
