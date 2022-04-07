<?php

/**
 * Components for admin interface.
 *
 * @link       https://gov.bc.ca
 * @since      1.0.0
 *
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/admin
 */

/**
 * Defines table component by extending WP native admin table.
 *
 *
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/admin
 * @author     Spencer <spencer.rose@gov.bc.ca>
 */
class WP_Basic_Forms_List_Table {

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct( $title="", $columns=[], $data=[], $default_sort=null ) {

        $this->title = $title;
        $this->data = $data;
        $this->columns = $columns;
        $this->sort_column = isset($_GET['column']) && in_array($_GET['column'], $columns)
            ? $_GET['column']
            : $default_sort;
        $this->sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc'
            ? 'DESC'
            : 'ASC';
    }

    /**
     * Print table to page.
     *
     * @param array|object $item
     * @param string $column_name
     * @return bool|mixed|string|void
     */

    function display() { ?>
        <div class="wrap">
            <h2><?php echo $this->title; ?></h2>
            <table class="ws_data_table">
                <tr>
                    <?php foreach ( $this->columns as $id => $column ): ?>
                        <th>
                            <a href="tablesort.php?column=name&order=<?php echo $this->sort_order; ?>">
                                <?php echo $column; ?>
                                <i class="fas fa-sort<?php
                                echo $column == $this->sort_column
                                    ? '-' . $this->sort_order
                                    : '';
                                ?>"></i>
                            </a>
                        </th>
                    <?php endforeach; ?>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
                <?php foreach ( $this->data as $id => $row ): ?>
                <tr>
                    <?php foreach ( $this->columns as $col_name => $column ): ?>
                    <td class="<?php echo $col_name == $this->sort_column ? 'sorted' : ''; ?>"><?php echo $row->$col_name; ?></td>
                    <?php endforeach; ?>
                    <td><input id="edit-<?php echo $row->form_id; ?>" class="button-primary" type="submit" value="Edit" onclick="ajax($(this));return false;"/>
                    </td>
                    <td><input id="delete-<?php echo $row->form_id; ?>" class="button-primary" type="submit" value="Delete" onclick="ajax($(this));return false;"/>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php
    }

}