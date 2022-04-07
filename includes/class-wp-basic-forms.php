<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://gov.bc.ca
 * @since      1.0.0
 *
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/includes
 * @author     Spencer <spencer.rose@gov.bc.ca>
 */
class WP_Basic_Forms {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Basic_Forms_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
    /**
     * @var array
     */
    private $settings;

    /**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WP_BASIC_FORMS_VERSION' ) ) {
			$this->version = WP_BASIC_FORMS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-basic-forms';

		$this->load_dependencies();
        $this->settings = array();
        $this->load_settings();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Basic_Forms_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Basic_Forms_i18n. Defines internationalization functionality.
	 * - Wp_Basic_Forms_Admin. Defines all hooks for the admin area.
	 * - Wp_Basic_Forms_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-basic-forms-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-basic-forms-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-basic-forms-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-basic-forms-public.php';

        /**
         * The class responsible for handling database operations of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-basic-forms-db.php';

        /**
         * The class responsible for handling database operations of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-basic-forms-model.php';

        /**
         * The class responsible for logging database errors and warnings.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-basic-forms-logger.php';

		$this->loader = new WP_Basic_Forms_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Basic_Forms_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Basic_Forms_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

        // Create event logger
        $logger = new WP_Basic_Forms_Logger();

        // Create data handler instance
        $db = new WP_Basic_Forms_Database(
                $this->get_plugin_name(),
                $this->get_version(),
                $logger
        );

        // Create model handler instance
        $model = new WP_Basic_Forms_Model(
            $this->get_plugin_name(),
            $this->get_version(),
            $db,
            $logger
        );

        // Create plugin admin instance
		$plugin_admin = new WP_Basic_Forms_Admin(
		    $this->get_plugin_name(),
            $this->get_version(),
            $this->get_settings(),
            $model,
            $logger
        );

        // Form action hooks
        $this->loader->add_action( 'rest_api_init', $plugin_admin, 'wpbf_register_routes' );

        // Plugin initialization
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'setup_admin_pages');

	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

        // Create event logger
        $logger = new WP_Basic_Forms_Logger();

        // Create data handler instance
        $db = new WP_Basic_Forms_Database(
            $this->get_plugin_name(),
            $this->get_version(),
            $logger
        );

        // Create model handler instance
        $model = new WP_Basic_Forms_Model(
            $this->get_plugin_name(),
            $this->get_version(),
            $db,
            $logger
        );

        // Create plugin admin instance
        $plugin_public = new WP_Basic_Forms_Public(
            $this->get_plugin_name(),
            $this->get_version(),
            $model,
            $logger
        );

        // Form action hooks
        $this->loader->add_action( 'rest_api_init', $plugin_public, 'wpbf_register_routes' );
        $this->loader->add_shortcode( 'wpbf_generate', $plugin_public, 'wpbf_generate_form' );

        // Plugin initialization
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );


	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    WP_Basic_Forms_Loader    Orchestrates the hooks of the plugin.
	 *@since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

    /**
     * The reference to the class that returns plugin configuration settings.
     *
     * @since     1.0.0
     * @return    array    Orchestrates the hooks of the plugin.
     */
    public function get_settings() {
        return $this->settings;
    }

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

    /**
     * Load fixed settings (JSON file)
     *
     * @since    1.0.0
     */
    private function load_settings() {
        try {
            $settings = file_get_contents(plugin_dir_path(__DIR__) . 'admin/settings.json');
            $this->settings = json_decode($settings, true);

        } catch (Exception $e) {
            $this->loader->add_action( 'admin_notices', $this, 'plugin_missing_settings_notice' );
        }
    }

    /* ==================================
	 * Plugin notices
	 *
	 * @since    1.0.0
	 * ================================== */

    public function plugin_missing_settings_notice() {
        ?>
        <div class="error notice">
            <p><?php _e( 'Initialization Failed. The JSON settings file is not found.', $this->get_plugin_name() ); ?></p>
        </div>
        <?php
    }

}
