<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://gov.bc.ca
 * @since      1.0.0
 *
 * @package    Wp_Basic_Forms
 * @subpackage Wp_Basic_Forms/admin/views
 */
?>

<div class="wrap">

    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
    </form>

</div><!-- .wrap -->
