<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://gov.bc.ca
 * @since      1.0.0
 *
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/admin
 */

include('components/class-wp-basic-forms-table.php');

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/admin
 * @author     Spencer <spencer.rose@gov.bc.ca>
 */
class WP_Basic_Forms_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private string $version;

    /**
     * Event logger.
     *
     * @since    1.0.0
     * @access   private
     * @var WP_Basic_Forms_Logger
     */
    private WP_Basic_Forms_Logger $logger;

    /**
     * Plugin configuration settings.
     *
     * @since    1.0.0
     * @access   private
     * @var object
     */
    private array $settings;

    /**
     * Plugin model instance.
     *
     * @since    1.0.0
     * @access   private
     * @var WP_Basic_Forms_Model
     */
    private WP_Basic_Forms_Model $model;

    /**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *@since    1.0.0
     */
	public function __construct(
        string                  $plugin_name,
        string                  $version,
        array                   $settings,
        WP_Basic_Forms_Model  $model,
        WP_Basic_Forms_Logger $logger
    ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = $settings;
        $this->logger = $logger;
        $this->model = $model;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Basic_Forms_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Basic_Forms_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-basic-forms-admin.css', array(), $this->version, 'all' );

	}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Wp_Basic_Forms_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Wp_Basic_Forms_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			wp_enqueue_script(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'js/wp-basic-forms-admin.js',
				array( 'jquery' ),
				$this->version,
				false
			);

        // create localized admin REST API object
        wp_localize_script( $this->plugin_name, 'wpbf_rest_api',
            array(
                    'rest_url' => rest_url(),
                    'rest_nonce' => wp_create_nonce( 'wp_rest' )
            )
        );

	}


    /**
     * Utility function used to register admin settings into a single collection.
     *
     * @since    1.0.0
     * @access   public
     */
    public function setup_admin_pages () {
        $menu_page = $this->settings['menu_page'];
        // Register page menu
        add_menu_page(
            $menu_page['page_title'],
            $menu_page['menu_title'],
            $menu_page['capability'],
            $menu_page['menu_slug'],
            array( $this, $menu_page['callback'] ),
            $menu_page['icon'],
            $menu_page['position'] );

        // Register page submenus
        foreach ( $this->settings['submenu_pages'] as $submenu_page ) {
            add_submenu_page(
                $menu_page['menu_slug'],
                $submenu_page['page_title'],
                $submenu_page['menu_title'],
                $submenu_page['capability'],
                $submenu_page['menu_slug'],
                array( $this, $submenu_page['callback']) );

            // Register fieldset sections in WP
            foreach ( $submenu_page['sections'] as $section ) {
                add_settings_section(
                    $section['id'],
                    $section['title'],
                    array( $this, $section['callback']),
                    $submenu_page['menu_slug'] );

                // Register option fields in WP
                foreach ( $section['settings'] as $settings ) {
                    add_settings_field(
                        $settings['id'],
                        $settings['title'],
                        array( $this, $settings['callback']),
                        $submenu_page['menu_slug'],
                        $section['id'],
                        $settings['args']);
                    register_setting( $submenu_page['menu_slug'], $settings['id'] );
                }
            }
        }
    }

    /**
     * Page sections callback
     **/
    public function section_callback( $args ) {
        foreach ($this->settings['submenu_pages'] as $submenu_page) {
            foreach ($submenu_page['sections'] as $section) {
                if ($section['id'] == $args['id']) {
                    echo '<hr />' . $section['description'];
                }
            }
        }
    }

    /**
     * Render WP Basic Forms admin pages
     *
     * @since    1.0.0
     */

    public function plugin_main_page_content() {
        include(plugin_dir_path(__FILE__) . 'views/wp-basic-forms-admin-display-main.php');
    }
    public function plugin_section_page_content() {
        include(plugin_dir_path(__FILE__) . 'views/wp-basic-forms-admin-display-section.php');
    }
    public function plugin_forms_page_content() {
        include(plugin_dir_path(__FILE__) . 'views/wp-basic-forms-admin-display-forms.php');
    }
    public function plugin_submissions_page_content() {
        include(plugin_dir_path(__FILE__) . 'views/wp-basic-forms-admin-display-submissions.php');
    }
    public function plugin_logs_page_content() {
        include(plugin_dir_path(__FILE__) . 'views/wp-basic-forms-admin-display-logs.php');
    }


    /**
     * Set up settings field callback: Render Checkbox Field
     **/
    public function wp_forum_api_checkbox_render( $field, $option_id, $selected, $disabled=False ) {
        ?>
        <label for="<?php echo $option_id; ?>">
            <input  type="checkbox" name="<?php echo $option_id; ?>" id="<?php echo $option_id; ?>"
                    value="1" <?php checked( $selected ); ?> <?php if( $disabled ) echo 'disabled'; ?> />
            <?php echo $field; ?>
        </label>
        <?php
        // Make checkbox readonly, but submit the value as selected
        if ( $selected && $disabled ) {
            ?>
            <input type="hidden" name="<?php echo $option_id; ?>" id="<?php echo $option_id; ?>" value="1" />
            <?php
        }
    }

    /**
     * Set up settings field callback: Render Text Field
     **/
    public function wp_basic_forms_textfield_render($args ) {
        ?>
        <input maxlength="300" size="60" name="<?php echo $args['id'] ?>" id="<?php echo $args['id'] ?>" type="text" value="<?php echo get_option( $args['id'] ); ?>" />
        <?php
    }

    /**
     * Set up settings field callback: Render Textarea Field
     **/
    public function wp_basic_forms_textarea_render($args ) {
        ?>
        <textarea rows="5" cols="33" name="<?php echo $args['id'] ?>" id="<?php echo $args['id'] ?>"><?php echo get_option( $args['id'] ); ?></textarea>
        <?php
    }

    /**
     * Set up settings field callback: Render WP Page Dropdown List
     **/
    public function wp_basic_forms_dropdown_pages_render( $args ) {
        return wp_dropdown_pages();
    }

    /**
     * Set up settings field callback: Render Notification Logs
     **/
    public function wp_basic_forms_logs_render( $args ) {
        ?>
        <p><b>Updates retrieved as of:</b> <?php echo date('Y-m-d', time() - 60 * 60 * 24); ?></p>
        <p class="forum_notifications"><?php
        $this->logger->print_logs();
        ?></p><?php
    }

    /**
     * Register WP REST API routes.
     **/
    public function wpbf_register_routes( $args ) {
        $namespace = 'wpbf/v1';

        // get form schema
        register_rest_route( $namespace, '/forms/view/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'wpbf_show_forms'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            ),
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }
        ) );

        // get all form schemas
        register_rest_route( $namespace, '/forms/view/all', array(
            'methods' => 'GET',
            'callback' => array($this, 'wpbf_show_forms'),
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }
        ) );

        // add new form
        register_rest_route( $namespace, '/forms/add', array(
            'methods' => 'POST',
            'callback' => array($this, 'wpbf_add_form'),
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }
        ) );

        // update form schema
        register_rest_route( $namespace, '/forms/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'wpbf_update_form'),
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }
        ) );

        // get all submissions
        register_rest_route( $namespace, '/submissions/view', array(
            'methods' => 'GET',
            'callback' => array($this, 'wpbf_show_submissions'),
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }
        ) );
    }

    /**
     * Set up callback: Get Forms Data
     **/
    public function wpbf_show_forms( $request_data ) {
        $response = $this->model->get_forms($request_data);
        return new WP_REST_Response($response);
    }

    /**
     * Set up callback: Add New Form
     **/
    public function wpbf_add_form( $request_data ) {
        $response = $this->model->add_form( $request_data );
        return new WP_REST_Response($response);
    }

    /**
     * Set up callback: Update Form Schema
     **/
    public function wpbf_update_form( $request_data ) {
        $response = $this->model->update_form( $request_data );
        return new WP_REST_Response($response);
    }

    /**
     * Set up callback: Get Submissions Data
     **/
    public function wpbf_show_submissions( $request_data ) {
        $response = $this->model->get_submissions();
        return new WP_REST_Response($response);
    }

}
