<?php

namespace BrokenLinkDetector;

class ListTable extends \WP_List_Table
{
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->getBrokenLinks();

        $perPage = 30;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ));
        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    public function getBrokenLinks()
    {
        global $wpdb;
        $tableName = \BrokenLinkDetector\App::$dbTable;
        $sql = "SELECT
                    links.*,
                    {$wpdb->posts}.*,
                    {$wpdb->posts}.ID AS post_id
                FROM $tableName links
                LEFT JOIN $wpdb->posts ON {$wpdb->posts}.ID = links.post_id
                ORDER BY {$wpdb->posts}.post_title";

        $result = $wpdb->get_results($sql);

        return $result;
    }

    public function get_columns()
    {
        return array(
            'post' => __('Post'),
            'url' => 'Url'
        );
    }

    public function get_hidden_columns()
    {
        return array();
    }

    public function get_sortable_columns()
    {
        return array(
            'post' => array('post', false),
            'url' => array('url', false)
        );
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'post':
                return '<a href="' . get_edit_post_link($item->post_id) . '"><strong>' . $item->post_title . '</strong></a>';

            default:
                return $item->$column_name;
        }
    }
}
