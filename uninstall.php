<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   PDF_Email_on_Save
 * @author    Matt Banks <mjbanks@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mattbanks.me
 * @copyright 2014 Matt Banks
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if (is_multisite()) {
	global $wpdb;
	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);

	if ($blogs) {
		foreach($blogs as $blog) {
			switch_to_blog($blog['blog_id']);

			// Delete all options
			delete_option( 'pdf_email_address' );
			delete_option( 'pdf_email_post_types' );

			restore_current_blog();
		}
	}
}
else
{
	// Delete all options
	delete_option( 'pdf_email_address' );
	delete_option( 'pdf_email_post_types' );
}
