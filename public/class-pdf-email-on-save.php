<?php
/**
 * PDF Email on Save
 *
 * @package   PDF_Email
 * @author    Matt Banks <mjbanks@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mattbanks.me
 * @copyright 2014-2017 Matt Banks
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
	const VERSION = '1.1.0';

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

		// Add an admin notice if PDF generation and sending just took place
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

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
		// No activation functionality needed... yet
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// No deactivation functionality needed... yet
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

		$email_option = get_option( 'pdf_email_address' );

		if ( false != $email_option ) {
			$email = $email_option;
		}
		else { // no option was set, use
			$email = get_bloginfo( 'admin_email' );
		}

		return $email;

	}

	/**
	 * Email PDF to user
	 *
	 * @since    1.0.0
	 */
	private function send_email( $email_address, $pdf, $post_id ) {

		// Get the post
		$post = get_post( $post_id );

		// Get site name and email for header
		$site_name =  get_bloginfo( 'name' );
		$admin_email =  get_bloginfo( 'admin_email' );

		// Set the subject
		$subject = $site_name . ' - PDF of ' . $post->post_title;

		// Create a filter to allow users to change the subject
		$subject = apply_filters( 'pdf_email_on_save_subject', $subject );

		// Set the message
		$message = 'Attached is a PDF of your post, ' . $post->post_title;

		// Create a filter to allow users to change the message
		$message = apply_filters( 'pdf_email_on_save_message', $message );

		// Set the PDF file name
		$filename = $post->post_name . '_' . get_the_date( 'd-m-Y_H-i' ) . '.pdf';

		// Create a filter to allow users to change the filename
		$filename = apply_filters( 'pdf_email_on_save_filename', $filename );

		// Set headers
		$uid = md5( uniqid( time() ) );

		$header = 'From: ' . $site_name . ' <' . $admin_email . '>' . "\n";
		$header .= "Reply-To: " . $admin_email . "\n";
		$header .= "MIME-Version: 1.0\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\n";

		$emessage .= "--".$uid."\n";
		$emessage .= "Content-type:text/plain; charset=iso-8859-1\n";
		$emessage .= "Content-Transfer-Encoding: 7bit\n\n";
		$emessage .= $message."\n\n";
		$emessage .= "--".$uid."\n";
		$emessage .= "Content-Type: application/pdf; name=\"".$filename."\"\n";
		$emessage .= "Content-Transfer-Encoding: base64\n";
		$emessage .= "Content-Disposition: attachment; filename=\"".$filename."\"\n\n";
		$emessage .= $pdf."\n\n";
		$emessage .= "--".$uid."--";

		// Send the email, return BOOL
		return mail( $email_address, $subject, $emessage, $header );

	}

	/**
	 * Create PDF from appropriate post type
	 *
	 * @since    1.0.0
	 */
	public function create_pdf( $post_id ) {

		// Include mPDF Class
		include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/mpdf/mpdf.php' );

		// Create new mPDF Document
		$mpdf = new mPDF();

		// Get the post
		$post = get_post( $post_id );

		// Setup our header
		$header = '<h1>' . get_bloginfo( 'name' ) . '</h1>';
		$header .= '<h1>' . get_bloginfo( 'description' ) . '</h1>';
		$header .= '<hr>';

		// Create a filter to allow users to change the header
		$header = apply_filters( 'pdf_email_on_save_header', $header );

		// Setup our content
		$content = '<h2>' . get_the_title() . '</h2>';
		$content .= '<p>' . get_the_date( 'l, F j, Y' ) . '</p>';
		$content .= apply_filters( 'the_content', $post->post_content );

		// Create a filter to allow users to change the content
		$content = apply_filters( 'pdf_email_on_save_content', $content );

		// Setup our footer
		$footer = '<hr>';
		$footer .= 'Link to page: ';
		$footer .= '<p>' . get_the_permalink( $post_id ) . '</p>';

		// Create a filter to allow users to change the footer
		$footer = apply_filters( 'pdf_email_on_save_footer', $footer );

		// Merge it all into the HTML
		$html = $header . $content . $footer;

		// Create a filter to allow users to change the html
		$html = apply_filters( 'pdf_email_on_save_html', $html );

		// Write HTML
		$mpdf->WriteHTML( $html );

		$full_content = $mpdf->Output( '', 'S' );

		$full_content = chunk_split( base64_encode( $full_content ) );

		return $full_content;

	}

	/**
	 * Create PDF from appropriate post type
	 *
	 * @since    1.0.0
	 *
	 * @param    int       $post_id The ID of the post.
	 */
	public function generate( $post_id ) {

		// bail out if running an autosave, ajax, cron, or revision
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// make sure the user is authorized
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Get post types saved in 'pdf_email_post_types' option
		$post_types = self::get_post_types();

		// Get the email address to use saved in 'pdf_email_address' option
		$email = self::get_email_address();

		// If the post type is in the saved $post_types array, create and email the pdf
		if ( isset( $_POST['post_type'] ) ) {

			if ( ! array_key_exists($_POST['post_type'], $post_types) ) {
				return;
			}

			// Make sure this is only when a post is published
			if ( isset( $_POST['post_status'] ) && 'publish' == $_POST['post_status'] ) {

				// Create the PDF and send the email
				$pdf = self::create_pdf( $post_id );

				// Send email if PDF exists
				if ( ! empty( $pdf ) ) {
					$email_status = self::send_email( $email, $pdf, $post_id );
				}

				// Flag whether PDF generation and email delivery
				if( ! empty( $pdf ) && ! empty( $email_status ) ) {
					// looks to be all good
					add_post_meta( $post_id, '_pdf_email_on_save_status', 'success' );
				} else {
					// something went wrong
					add_post_meta( $post_id, '_pdf_email_on_save_status', 'fail' );
				}
			}

		}

	}

	/**
	 * Output applicable admin notice after PDF generation and email delivery
	 *
	 * @since    1.0.0
	 */
	function admin_notices() {

		// Triggered by being on the post edit screen and there being a post ID to work with
		$screen = get_current_screen();
		if ( 'post' !== $screen->base || ! isset( $_GET['post'] ) ) {
			return;
		}

		// Look for our post meta breadcrumb
		$post_id = absint( $_GET['post'] );
		$status = get_post_meta( $post_id, '_pdf_email_on_save_status', true );

		// Clean up (and prevent false positive checks on subsequent page loads)
		if ( $status ) {
			delete_post_meta( $post_id, '_pdf_email_on_save_status' );
		}

		// Output the appropriate admin notice
		if ( 'success' == $status ) {
			?>
			<div class="updated">
				<p><?php _e( 'PDF sent successfully by email.', self::get_plugin_slug() ); ?></p>
			</div>
			<?php
		}
		elseif ( 'fail' == $status ) {
			?>
			<div class="error">
				<p><?php _e( 'Oops! Something went wrong. PDF was not sent by email.', self::get_plugin_slug() ); ?></p>
			</div>
			<?php
		}
	}

}
