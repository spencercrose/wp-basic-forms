<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://gov.bc.ca
 * @since      1.0.0
 *
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/public
 * @author     Spencer <spencer.rose@gov.bc.ca>
 */
class WP_Basic_Forms_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct(
	    string $plugin_name,
        string $version,
        WP_Basic_Forms_Model $model,
        WP_Basic_Forms_Logger $logger
    ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->logger = $logger;
        $this->model = $model;

	}

	public function get_select($form_id, $field)
    {
        $label = $field->label ?? "";
        $fieldname = $field->name ?? "";
        $field_id = 'wpbf_form_' . $form_id . '_' . $fieldname;

        // create options list
        $options = $field->options ?? [];
        $options_rendered = "<option disabled selected value> -- select an option -- </option>";
        foreach ($options as $option_key => $option) {
            $option_id = $fieldname . '_' . $option_key;
            $options_rendered .= "<option id=\"$option_id\">$option</option>";
        }

        return "<label for=\"$field_id\">$label</label><select id=\"$field_id\" name=\"$fieldname\" >$options_rendered</select>";
    }

    public function get_multiselect($form_id, $field)
    {
        $label = $field->label ?? "";
        $fieldname = $field->name ?? "";
        $other = $field->other ?? null;
        $field_id = 'wpbf_form_' . $form_id . '_' . $fieldname;

        // create options list
        $options = $field->options ?? [];
        $multiselect = "<h3>$label</h3>";
        foreach ($options as $option_key => $option) {
            $option_id = $field_id . '_' . $option_key;
            $multiselect .= "<label for=\"$option_id\"><input id=\"$option_id\" type=\"checkbox\" class=\"wpbf_data_field\" name=\"$fieldname\"  />$option</label>";
        }

        // include other field (if requested)
        if ($other) {
            $other_option = $other->option ?? "Other";
            $other_label = $other->label ?? "Please specify";
            $multiselect .= "<label for=\"$field_id-other\"><input id=\"$field_id-other\" class=\"wpbf_data_field\" type=\"checkbox\" name=\"$fieldname\"  />$other_option</label>";
            $multiselect .= "<label for=\"$field_id-other_value\">$other_label</label><input id=\"$field_id-other_value\" class=\"wpbf_data_field\" type=\"text\" name=\"$fieldname\"  />";
        }

        return $multiselect;
    }

    public function get_input($form_id, $field, $repeat="")
    {
        $type = $field->type ?? "";
        $label = $field->label ?? "";
        $fieldname = isset($field->name) ? $field->name . '_' . $repeat : "";
        $placeholder = $field->placeholder ?? "";
        $field_id = 'wpbf_form_' . $form_id . '_' . $fieldname;
        $min = $field->min ?? 0;
        $max = $field->max ?? 0;
        $inputs = array(
            "text" => "<label for=\"$field_id\">$label</label><input id=\"$field_id\" type=\"text\" class=\"wpbf_data_field\" name=\"$fieldname\" placeholder=\"$placeholder\" />",
            "checkbox" => "<label for=\"$field_id\"><input id=\"$field_id\" type=\"checkbox\" class=\"wpbf_data_field\" name=\"$fieldname\" />$label</label>",
            "integer" => "<label for=\"$field_id\">$label</label><input id=\"$field_id\" class=\"wpbf_data_field\" type=\"number\" name=\"$fieldname\" placeholder=\"$placeholder\" min=\"$min\" max=\"$max\" />",
            "select" => $this->get_select($form_id, $field),
            "multiselect" => $this->get_multiselect($form_id, $field)
        );
        return $inputs[$type];
    }

    /**
     * Render the submission form.
     *
     * @since    1.0.0
     */
    public function render( $form_id, $schema ) {

        $config_json = $schema->config ?? "{}";
        $config = json_decode($config_json);
        $fieldsets = $config->fieldsets ?? [];

        echo "<form id=\"wpbf_submission_form\" class=\"wpbf-form\">";
        // include form ID
        echo "<input type=\"hidden\" id=\"wpbf_form_id\" value=\"$form_id\" />";

        foreach ($fieldsets as $fieldset_key => $fieldset) {
            $fieldset_type = $fieldset->type ?? "{}";
            $fieldset_id = 'wpbf_form_' . $form_id . '_fieldset_' . $fieldset_key;
            $title = $fieldset->title ?? "";
            $legend = $fieldset->legend ?? "";
            $fields = $fieldset->fields ?? [];

            echo "<fieldset id=\"$fieldset_id\" class=\"wpbf_form\">";
            echo "<legend>$title</legend>";

            // Repeatable fieldsets
            if ($fieldset_type === 'repeatable') {

                // create repeater input
                $repeater = $fieldset->repeater ?? [];
                $init_repeat = $fieldset->init_repeat ?? 1;
                echo "<div class=\"wpbf_repeater_input\">";
                echo $this->get_input($form_id, $repeater);
                echo "</div>";
                echo "<div class=\"wpbf_repeatable_container\"></div>";

                // create global JQuery object repeated fieldset template
                ?><script>
                    (function( $ ) {
                    'use strict';
                    var $wpbf_repeatable = $($.parseHTML('<fieldset class="wpbf_repeatable"><legend><?php echo $legend ?></legend><div><button class="accordion-toggle">Expand</button></div><div class="accordion-data"><?php
                        foreach ($fields as $field) {
                            echo $this->get_input($form_id, $field);
                        }
                        ?></div></fieldset>'));

                        /**
                         * Repeater action
                         */

                        $(function() {

                            $(".wpbf_repeater_input")
                                .on('change', 'select', function(e){
                                    e.preventDefault();
                                    var repeat_count = $(this).find(":selected").text();
                                    // var $template = $repeatables[0].clone();
                                    console.log(repeat_count)
                                    var $rep_container = $(".wpbf_repeatable_container");
                                    for (var i=1; i <= repeat_count; i+=1) {
                                        $rep_container.append($wpbf_repeatable.clone());
                                    }

                                });

                            $(".wpbf_repeatable_container")
                                .on('click', '.accordion-toggle', function(e){
                                    e.preventDefault();
                                    console.log('Expand', $(this).find('.accordion-data'));
                                    $(this).find('.accordion-data').addClass('hide');
                                });
                        });


                    })( jQuery );
                </script><?php

            }
            // Default (non-repeatable) fieldsets
            else {
                foreach ($fields as $field) {
                    echo $this->get_input($form_id, $field);
                }
            }
            echo "</fieldset>";
        }

        echo "<div class=\"success_msg notice notice-success inline\" style=\"display: none\"><p>Form submitted successfully.</p></div>";
        echo "<div class=\"error_msg notice notice-error inline\" style=\"display: none\"><p>Error: Form could not be submitted.</p></div>";
        echo "<fieldset><input type=\"submit\" name=\"wpbf_$form_id-submit\" value=\"Register\" /></fieldset>";
        echo "</form>";
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-basic-forms-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/***
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Basic_Forms_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Basic_Forms_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-basic-forms-public.js', array( 'jquery' ), $this->version, true );

        // create localized admin REST API object
        wp_localize_script( $this->plugin_name, 'wpbf_rest_api',
            array(
                'rest_url' => rest_url(),
                'rest_nonce' => wp_create_nonce( 'wp_rest' )
            )
        );
	}

    /**
     * Register WP REST API routes.
     **/
    public function wpbf_register_routes( $args ) {
        $namespace = 'wpbf/v1';

        // get form schema
        register_rest_route( $namespace, '/forms/view/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'wpbf_show_form'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric( $param );
                    }
                ),
            ),
            'permission_callback' => true
        ) );

        // add new submission
        register_rest_route( $namespace, '/submissions/add', array(
            'methods' => 'POST',
            'callback' => array($this, 'wpbf_add_submission'),
            'permission_callback' => function () {
                return true;
            }
        ) );
    }

    /**
     * Set up callback: Submit New Submission
     **/
    public function wpbf_add_submission( $request_data ) {
        $response = $this->model->add_submission( $request_data );
        return new WP_REST_Response($response);
    }

    /**
     * Set up callback: Get Forms Data
     **/
    public function wpbf_show_form( $request_data ) {
        $response = $this->model->get_forms($request_data);
        return new WP_REST_Response($response);
    }

    /**
     * Shortcode: Generate form inputs.
     **/
    public function wpbf_generate_form( $atts = array() ) {
        $form_id = $atts['schema'];
        // Get form schema data
        $response = $this->model->get_forms( array('form_id' => $form_id) );
        $schema = $response['data'][0] ?? array();
        $this->render($form_id, $schema);
    }

}
