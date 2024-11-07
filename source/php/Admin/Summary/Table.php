<?php 

namespace BrokenLinkDetector\Admin\Summary;

use BrokenLinkDetector\Database\Database;
use BrokenLinkDetector\Config\Config;

class Table extends \WP_List_Table
{
    public function __construct(private Database $db, private Config $config)
    {
        parent::__construct([
            'singular' => __('Broken Link', 'broken-link-detector'),
            'plural'   => __('Broken Links', 'broken-link-detector'),
            'ajax'     => false,
        ]);
    }

    public function getBrokenLinks(): array
    {
        $maxLimit = null;

        $results = $this->db->getInstance()->get_results(
            $this->db->getInstance()->prepare("
                SELECT * FROM 
                " . $this->db->getInstance()->prefix . $this->config->getTableName() . " 
                WHERE http_code != 200
                ORDER BY time ASC
                LIMIT %d", 
                (is_int($maxLimit) ? $maxLimit : PHP_INT_MAX)
            )
        );

        return $results;
    }

    public function prepare_items(): void
    {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];
        
        $data = $this->getBrokenLinks();

        // Populate the table items
        $this->items = $data;
    }

    public function get_columns(): array
    {
        return [
            'post_id'    => __('Occurs On Page', 'broken-link-detector'),
            'url'        => __('URL', 'broken-link-detector'),
            'http_code'  => __('HTTP Code', 'broken-link-detector'),
            'time'       => __('Last Checked', 'broken-link-detector'),
        ];
    }

    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            
            case 'url':
            case 'http_code':
            case 'time':
                return $item->$column_name;
              case 'post_id':
                return '<a href="' . get_permalink($item->post_id) . '" target="_blank">' . get_the_title($item->post_id) . '</a>';
            default:
                return print_r($item, true);
        }
    }
}