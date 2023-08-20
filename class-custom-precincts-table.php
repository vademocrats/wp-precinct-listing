<?php
class Custom_Precincts_Table extends WP_List_Table {
    function __construct() {
        parent::__construct(array(
            'singular' => 'precinct',
            'plural' => 'precincts',
            'ajax' => true
        ));
    }

    function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_precincts';

        $per_page = 10;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();

        $current_page = $this->get_pagenum();
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        $data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY precinct_number ASC LIMIT $per_page OFFSET " . (($current_page - 1) * $per_page));

        $this->items = $data;

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));
    }

    function get_columns() {
        $columns = array(            
            'precinct_number' => 'Number',
            'precinct_name' => 'Name',
            'precinct_captain' => 'Captain',
            'precinct_deputy' => 'Deputy',
            'precinct_location' => 'Location',
            'precinct_region' => 'Region',
            'actions' => 'Actions'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = [
            'precinct_number' => ['precinct_number', true],
            'precinct_name' => ['precinct_name', true],
            'precinct_captain' => ['precinct_captain', true],
            'precinct_deputy' => ['precinct_deputy', true],
            'precinct_region' => ['precinct_region', true],
        ];

        return $sortable_columns;
    }

    function column_default($item, $column_name) {
        return $item->$column_name;
    }

    function column_actions($item) {
        $actions = array(
            'edit' => sprintf('<a href="#" class="edit-precinct" data-entry-id="%s">Edit</a>', $item->id),
            'delete' => sprintf(
                '<a href="#" class="delete-precinct action-delete" data-entry-id="%s" data-nonce="'.wp_create_nonce('delete_precinct_' . $item->id).'">%s</a>',absint($item->id),__('Delete', 'text-domain')
                )
        );

        return $this->row_actions($actions);
    }

    // Sorting function
    protected function usort_reorder($a, $b) {
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'precinct_number';
        $order = isset($_GET['order']) ? $_GET['order'] : 'asc';
        $result = strcmp($a->$orderby, $b->$orderby);

        return ($order === 'asc') ? $result : -$result;
    }

    // Render the table rows
    function display_rows() {
        $records = $this->items;
        usort($records, array($this, 'usort_reorder'));

        foreach ($records as $item) {
            echo '<tr>';
            $this->single_row_columns($item);
            echo '</tr>';
        }
    }
}
?>
