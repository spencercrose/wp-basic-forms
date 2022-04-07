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
 * The model plugin class.
 *
 * This is used to define the model logic layer.
 *
 *
 * @since      1.0.0
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/includes
 * @author     Spencer <spencer.rose@gov.bc.ca>
 */
class WP_Basic_Forms_Model {

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
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Basic_Forms_Database    $db    The current version of the plugin.
     */
    private WP_Basic_Forms_Database $db;

    /**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct(        string                  $plugin_name,
                                        string                  $version,
                                        WP_Basic_Forms_Database $db,
                                        WP_Basic_Forms_Logger $logger
    ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->db = $db;
		$this->logger = $logger;

	}


    /**
     * Retrieve current active forms.
     *
     *
     * @since    1.0.0
     * @access   private
     */

    public function get_forms ( $request_data = null ) {

        $form_id = $request_data && isset($request_data['form_id'])
            ? $request_data['form_id']
            : null;

        $schema = array(
            'form_id' => 'Form ID',
            'form_name' => 'Form Name',
            'post_id' => 'Post ID',
            'timestamp' => 'Created'
        );

        // query forms in db
        $response = $form_id
            ? $this->db->get_forms( $form_id )
            : $this->db->get_forms();

        // Handle db errors
        $error = null;
        if ( is_wp_error($response) ){
            $error = new WP_Error( '400', 'An error has occurred.', $response );
        }

        return array(
            'form_id' => $form_id,
            'error' => $error,
            'schema' => $schema,
            'data' => $response,
            'default_sort' => 'form_id'
        );

    }

    /**
     * Create a new form.
     *
     *
     * @since    1.0.0
     * @access   private
     */

    public function add_form ( $request_data ) {
        $form_id = $request_data['form_id'];
        $form_name = $request_data['form_name'];
        $config = $request_data['config'];
        return $this->db->add_form( $form_id, $form_name, $config );
    }

    /**
     * Update form schema.
     *
     *
     * @since    1.0.0
     * @access   private
     */

    public function update_form ( $request_data ) {
        $form_id = $request_data['form_id'];
        $config = $request_data['config'];
        return $this->db->update_form( $form_id, $config );
    }

    /**
     * Delete form.
     *
     *
     * @since    1.0.0
     * @access   private
     */
    private function delete_form ( $request_data ) {
        $form_id = $request_data['form_id'];
        return $this->db->delete_form( $form_id );
    }

    /**
     * Get form submission(s).
     *
     *
     * @since    1.0.0
     * @access   private
     */
    public function get_submissions( $submission_id = null ) {

        $schema = array(
            'submission_id' => 'ID',
            'form_id' => 'Form ID',
            'form_name' => 'Form Name',
            'timestamp' => 'Created'
        );

        $response = $this->db->get_submissions( $submission_id );

        // Handle db errors
        $error = null;
        if ( is_wp_error($response) ){
            $error = new WP_Error( '400', 'An error has occurred.', $response );
        }

        return array(
            'error' => $error,
            'schema' => $schema,
            'data' => $response,
            'default_sort' => 'form_name'
        );
    }

    /**
     * Insert form submission.
     *
     *
     * @since    1.0.0
     * @access   private
     */
    public function add_submission( $request_data ) {
        $form_id = $request_data['form_id'];
        $data = json_encode($request_data['data']);
        $metadata = json_encode($request_data['metadata']);
        return $this->db->add_submission( $form_id, $data, $metadata );
    }

}
