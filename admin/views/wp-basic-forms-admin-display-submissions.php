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
      <h2><?php echo get_admin_page_title(); ?></h2>
        <div id="wpbf_submissions_list"></div><!-- .wpbf_submissions_list -->
    </div>
