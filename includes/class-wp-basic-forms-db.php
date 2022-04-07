<?php

/**
 * The file that defines the database plugin class
 *
 * A class definition that includes attributes and functions for
 * handling database functionality.
 *
 * @link       https://gov.bc.ca
 * @since      1.0.0
 *
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/includes
 */

/**
 * The database plugin class.
 *
 * This is used to define the data layer for persistence interactions.
 *
 *
 * @since      1.0.0
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/includes
 * @author     Spencer <spencer.rose@gov.bc.ca>
 */
class WP_Basic_Forms_Database {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected string $version;

    protected WP_Basic_Forms_Logger $logger;
    protected string $forms_table;
    protected string $data_table;
    protected string $charset_collate;

    /**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct($plugin_name, $version, $logger) {

        global $wpdb;

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Initialize table settings
        $this->forms_table = $wpdb->prefix . "wpbf_forms";
        $this->data_table = $wpdb->prefix . "wpbf_data";
        $this->charset_collate = $wpdb->get_charset_collate();

		$this->logger = $logger;

		# initialize databases
		$this->install();

	}

	/**
	 * Install required database tables.
	 *
	 *
	 * @since    1.0.0
	 * @access   private
	 */

	private function install () {

        $sql_forms = "CREATE TABLE IF NOT EXISTS $this->forms_table (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          form_id varchar(256) NOT NULL UNIQUE CHECK (form_id <> ''),
          form_name varchar(256) NOT NULL CHECK (form_name <> ''),
          config json NOT NULL CHECK (JSON_VALID(config)),
          timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          PRIMARY KEY  (id)
        ) $this->charset_collate;";

        $sql_data = "CREATE TABLE IF NOT EXISTS $this->data_table (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          form_id varchar(256) NOT NULL,
          user_id varchar(256) NOT NULL,
          data json NOT NULL CHECK (JSON_VALID(data)),
          metadata json NOT NULL CHECK (JSON_VALID(metadata)),
          timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          PRIMARY KEY (id),          
          CONSTRAINT `fk_form_id`
            FOREIGN KEY (form_id) REFERENCES $this->forms_table (form_id)
            ON DELETE CASCADE
            ON UPDATE RESTRICT
        ) $this->charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql_forms );
        dbDelta( $sql_data );

	}

    /**
     * Retrieve forms.
     *
     *
     * @since    1.0.0
     * @access   private
     */

    public function get_forms ( $form_id=null ) {
        global $wpdb;

        $sql = $form_id
            ? $wpdb->prepare("SELECT * FROM $this->forms_table WHERE `form_id` = %d", array( $form_id ))
            : "SELECT * FROM $this->forms_table";

        return $wpdb->get_results($sql);
    }

    /**
     * Insert form schema.
     *
     *
     * @since    1.0.0
     * @access   private
     */

    public function add_form ( $form_id=null, $form_name=null, $form_config=null ) {
        global $wpdb;
        return $wpdb->insert(
            $this->forms_table,
            array(
                'form_id' => $form_id,
                'form_name' => $form_name,
                'config' => $form_config,
                'timestamp' => current_time('mysql'),
            )
        );
    }

    /**
     * Update form schema.
     *
     *
     * @since    1.0.0
     * @access   private
     */

    public function update_form ( $form_id=null, $form_config=null ) {
        global $wpdb;
        return $wpdb->update(
            $this->forms_table,
            array(
                'config' => $form_config,
                'timestamp' => current_time('mysql'),
            ),
            array(
                'form_id' => $form_id
            )
        );
    }

    /**
     * Delete form.
     *
     *
     * @since    1.0.0
     * @access   private
     */
    public function delete_form ( $form_id ) {
        global $wpdb;

        // Delete the form.
        return $wpdb->query(
            $wpdb->prepare("
                DELETE FROM $this->forms_table
                WHERE form_id = %s
            ",
                $form_id
            )
        );

    }

    /**
     * Retrieve form submissions.
     *
     *
     * @since    1.0.0
     * @access   private
     */
    public function get_submissions ( $submission_id=null ) {
        global $wpdb;

        $sql = $submission_id
            ? $wpdb->prepare("SELECT *, dt.id as submission_id FROM $this->data_table as dt 
                    WHERE `id` = %d JOIN $this->forms_table as ft
                    ON dt.form_id = ft.form_id", array( $submission_id )
            )
            : "SELECT *, dt.id as submission_id FROM $this->data_table as dt
                JOIN $this->forms_table as ft
                    ON dt.form_id = ft.form_id
                ";

        return $wpdb->get_results($sql);
    }

    /**
     * Insert form submission.
     *
     *
     * @since    1.0.0
     * @access   private
     */
    public function add_submission ($form_id, $data_json, $metadata_json) {
        global $wpdb;
        return $wpdb->insert(
            $this->data_table,
            array(
                'form_id' => $form_id,
                'data' => $data_json,
                'metadata' => $metadata_json,
                'timestamp' => current_time( 'mysql' ),
            )
        );
    }

}
