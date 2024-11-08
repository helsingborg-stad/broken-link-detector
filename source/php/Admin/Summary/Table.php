<?php 

namespace BrokenLinkDetector\Admin\Summary;

use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Config\Config;
use WpService\WpService;

class Table extends \WP_List_Table
{
    public function __construct(private WpService $wpService, private Database $db, private Config $config)
    {
        parent::__construct([
            'singular' => __('Broken Link', 'broken-link-detector'),
            'plural'   => __('Broken Links', 'broken-link-detector'),
            'ajax'     => false,
        ]);
    }

    public function getBrokenLinks(): array
    {
      // Filtering by http_code if selected
      $httpCodeFilter = isset($_REQUEST['http_code_filter']) ? absint($_REQUEST['http_code_filter']) : null;

      // Sorting parameters
      $orderby = !empty($_REQUEST['orderby'])
          ? sanitize_sql_orderby($_REQUEST['orderby'])
          : 'time';
      $order = !empty($_REQUEST['order']) && in_array(strtoupper($_REQUEST['order']), ['ASC', 'DESC'])
          ? strtoupper($_REQUEST['order'])
          : 'ASC';

      //Build the query
      $query = $this->db->getInstance()->prepare(
        "SELECT * FROM {$this->db->getTableName()} WHERE http_code != %d"
      , 200);

      //Add filter if selected
      if ($httpCodeFilter) {
        $query .= $this->db->getInstance()->prepare(" AND http_code = %d", $httpCodeFilter);
      }

      //Add sorting
      $query .= " ORDER BY {$orderby} {$order}";
      
      return $this->db->getInstance()->get_results($query);
    }

    private function retriveHttpCodes(): array
    {
        $result = $this->db->getInstance()->get_col(
            "SELECT DISTINCT http_code FROM {$this->db->getTableName()}"
        );
        return array_map('absint', $result ?? []);
    }

    public function prepare_items(): void
    {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->items = $this->getBrokenLinks();
    }

    public function get_columns(): array
    {
        return [
            'post_id'   => __('Post', 'broken-link-detector'),
            'url'       => __('URL', 'broken-link-detector'),
            'http_code' => __('HTTP Code', 'broken-link-detector'),
            'time'      => __('Last Checked', 'broken-link-detector'),
        ];
    }

    public function get_sortable_columns(): array
    {
        return [
            'http_code' => ['http_code', false],
            'time'      => ['time', false],
        ];
    }

    protected function column_default($item, $column_name)
    {
        if ($column_name === 'post_id') {
            // Retrieve the permalink for the post ID and display as clickable link
            $postId = $item->post_id;
            if ($postId) {
                $permalink = get_permalink($postId);
                $title = get_the_title($postId);
                return sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    esc_url($permalink),
                    esc_html($title ?: __('View Post', 'broken-link-detector'))
                );
            }
            return __('N/A', 'broken-link-detector');
        }

        if ($column_name === 'http_code') {
            return $this->wpService->getStatusHeaderDesc($item->$column_name) . " (" . $item->$column_name . ")";
        }

        return $item->$column_name ?? '';
    }

    protected function extra_tablenav($which): void
    {
        if ($which === 'top') {
            // Dropdown filter for HTTP Code
            $selectedCode = isset($_REQUEST['http_code_filter']) ? absint($_REQUEST['http_code_filter']) : '';
            echo '<div class="alignright actions">';
              echo '<select name="http_code_filter">';
                echo '<option value="">' . __('All HTTP Codes', 'broken-link-detector') . '</option>';
                $httpCodes = $this->retriveHttpCodes();
                foreach ($httpCodes as $code) {
                    echo '<option value="' . $code . '"' . selected($selectedCode, $code, false) . '>' . $code . ' - '.$this->wpService->getStatusHeaderDesc($code).'</option>';
                }
              echo '</select>';
                submit_button(__('Filter'), 'button', '', false);
            echo '</div>';
        }
    }
}