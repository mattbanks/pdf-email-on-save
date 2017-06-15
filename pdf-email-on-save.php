<?php
/**
 * PDF Email on Save
 *
 * Create a PDF based on the print stylesheet for a given post, page,
 * or custom post type when the content is saved. User can select which
 * post types are included and which email to send the PDF to via settings.
 *
 * @package   PDF_Email_on_Save
 * @author    Matt Banks <mjbanks@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mattbanks.me
 * @copyright 2014-2017 Matt Banks
 *
 * @wordpress-plugin
 * Plugin Name:       PDF Email On Save
 * Plugin URI:        http://mattbanks.me
 * Description:       Plugin to create a PDF for a given post, page, or custom post type when the content is saved and email it to the user.
 * Version:           1.1.0
 * Author:            Matt Banks
 * Author URI:        http://mattbanks.me
 * Text Domain:       pdf-email-on-save-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/mattbanks/pdf-email-on-save
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-pdf-email-on-save.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'PDF_Email', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PDF_Email', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'PDF_Email', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-pdf-email-on-save-admin.php' );
	add_action( 'plugins_loaded', array( 'PDF_Email_Admin', 'get_instance' ) );

}
