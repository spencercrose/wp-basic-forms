<?php

/**
 * Provide admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Basic_Forms
 * @subpackage WP_Basic_Forms/admin/views
 */

?>
  <?php settings_errors(); global $plugin_page; ?>
    <div class="wrap">

        <div class="success_msg notice notice-success inline" style="display: none">
            <p>Form Created Successfully</p>
        </div>
        <div class="error_msg notice notice-error inline" style="display: none">
            <p>Error: Form could not be saved.</p>
        </div>

        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <h2>Create a New Form</h2>
        <form id="wpbf_add_form" method="post" action="" class="wpbf-form" enctype="multipart/form-data">
                <fieldset>
                    <label for="add_form_id">New Form ID</label>
                    <input required="required" id="add_form_id" type="text" name="form_id" value="" />
                    <label for="add_form_name">New Form Name</label>
                    <input required="required" id="add_form_name" type="text" name="form_name" value="" />
                    <label for="add_form_config">Form Schema (JSON)</label>
                    <textarea required="required" id="add_form_config" cols="40" name="form_config"></textarea>
                    <input type="submit" value="Add Form" />
                </fieldset>
        </form><!-- #wpbf_add_form -->

        <h2>Current Forms</h2>
        <div id="wpbf_forms_list"></div><!-- #wpbf_forms_list -->

    </div><!-- .wrap -->
