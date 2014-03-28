<?php
/**
 * PDF Email on Save
 *
 * @package   PDF_Email
 * @author    Matt Banks <mjbanks@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mattbanks.me
 * @copyright 2014 Matt Banks
 */

/**
 * PDF Email on Save class
 *
 * @package PDF_Email
 * @author  Matt Banks <mjbanks@gmail.com>
 */
class PDF_Email {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'pdf-email-on-save';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		/*
		 * Trigger PDF creation and emailing when appropriate posts are saved
		 */
		add_action( 'save_post', array( $this, 'generate' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Get post types saved in 'pdf_email_post_types' option
	 *
	 * @since    1.0.0
	 *
	 * @return   array    Array of post types
	 */
	private function get_post_types() {

		return get_option( 'pdf_email_post_types' );

	}

	/**
	 * Get the email address to use saved in 'pdf_email_address' option
	 *
	 * @since    1.0.0
	 *
	 * @return   string    Email addresses for mailing PDF. If 'pdf_email_address'
	 *                     is not set, return 'admin_email'
	 */
	private function get_email_address() {

		return get_option( 'pdf_email_address' );

	}

	/**
	 * Email PDF to user
	 *
	 * @since    1.0.0
	 */
	private function send_email( $email_address, $pdf ) {
		// @TODO: Send email
	}

	/**
	 * Create PDF from appropriate post type
	 *
	 * @since    1.0.0
	 */
	public function create_pdf( $post_id ) {

		// Include mPDF Class
		include_once( plugins_url( 'includes/mpdf/mpdf.php' ) );

		// Create new mPDF Document
		$mpdf = new mPDF();

		// Beginning Buffer to save PHP variables and HTML tags
		ob_start();

		$post = get_post( $post_id );

		echo "<h1>Today's Specials</h1>";
		echo '<h2>' . date( 'n/j/y' ) . '</h2>';

		echo '<h3>Soups</h3>';
		echo $cfs->get( 'specials_soups', $post_id );

		echo '<h3>Sandwiches</h3>';
		echo '<p>' . $cfs->get( 'sandwich_special_1', $post_id ).  '</p>';
		echo '<p>' . $cfs->get( 'sandwich_special_2', $post_id ).  '</p>';
		echo '<p>' . $cfs->get( 'sandwich_special_3', $post_id ).  '</p>';

		echo '<h3>Prepared Food</h3>';
		echo $cfs->get( 'specials_prepared_food', $post_id );

		// Get contents, end buffer
		$html = ob_get_contents();
		ob_end_clean();

		// Encode contents as UTF-8
		$mpdf->WriteHTML( utf8_encode( $html ) );

		$content = $mpdf->Output( '', 'S' );

		$content = chunk_split( base64_encode( $content ) );

		return $content;

	}

	/**
	 * Create PDF from appropriate post type
	 *
	 * @since    1.0.0
	 */
	public function generate( $post_id ) {

		// bail out if running an autosave, ajax, cron, or revision
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post->ID;
		}

		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return $post->ID;
		}

		if( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return $post->ID;
		}

		if( wp_is_post_revision( $post_id ) ) {
			return $post->ID;
		}

		// make sure the user is authorized
		if( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		// Get post types saved in 'pdf_email_post_types' option
		$post_types = self::get_post_types();

		// Get the email address to use saved in 'pdf_email_address' option
		$email = self::get_email_address();

		// Create the PDF
		$pdf = self::create_pdf( $post_id );

		// Send email
		self::send_email( $email, $pdf );

		return $post->ID;
	}

}
