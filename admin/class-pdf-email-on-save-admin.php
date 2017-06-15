<?php
/**
 * PDF Email on Save
 *
 * @package   PDF_Email_Admin
 * @author    Matt Banks <mjbanks@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mattbanks.me
 * @copyright 2014-2017 Matt Banks
 */

/**
 * PDF Email on Save Admin class
 *
 * @package PDF_Email_Admin
 * @author  Matt Banks <mjbanks@gmail.com>
 */
class PDF_Email_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = PDF_Email::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * Register settings
		 */
		add_action( 'admin_init', array( $this, 'add_settings' ) );

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
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'PDF Email on Save', $this->plugin_slug ),
			__( 'PDF Email on Save', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * Callback to output content above our settings section fields
	 *
	 * @see add_settings()
	 *
	 * @since    1.0.0
	 */
	public function settings_section_callback() {

		print 'Use the following to customize your settings for PDF Email on Save:';

	}

	/**
	 * Callback to output the markup of our email address field
	 *
	 * @see add_settings()
	 *
	 * @since    1.0.0
	 */
	public function email_address_field_callback() {

		$setting = esc_attr( get_option( 'pdf_email_address' ) );

		if ( false == $setting ) {
			// the setting was never set
			$setting = esc_attr( get_option( 'admin_email' ) );
		}

		echo "<input type='text' name='pdf_email_address' id='pdf_email_address' value='$setting' class='regular-text'>";
		echo '<p class="description">Enter email address to send PDF when post is saved. For multiple email addresses, separate them with a comma. Default is the WordPress admin email address.</p>';

	}

	/**
	 * Sanitize our email address settings before they're saved to the database
	 *
	 * @param $input
	 * @uses sanitize_text_field() to sanitize our text field
	 *
	 * @since    1.0.0
	 *
	 * @return	 string 	sanitized email field data
	 */
	public function email_address_sanitize( $input ) {

		if ( ! is_string( $input ) ) {
			$input = (string) $input;
		}

		$input = sanitize_text_field( $input );

		return $input;

	}

	/**
	 * Callback to output the markup of our post types field
	 *
	 * @see add_settings()
	 *
	 * @since    1.0.0
	 */
	public function post_types_field_callback() {

		$post_types = get_option( 'pdf_email_post_types' );

		if ( false == $post_types || ! is_array( $post_types ) ) {
			// the setting was never set
			$post_types = array();
		}

		$all_post_types = get_post_types( array( 'public' => true ) );

		echo '<fieldset>';

		foreach ( $all_post_types as $post_type ) {
			?>

			<label for="<?php echo $post_type; ?>">
				<input type="checkbox" name="pdf_email_post_types[<?php echo $post_type; ?>]" id="pdf_email_post_types[<?php echo $post_type; ?>]" value="1" <?php checked( isset( $post_types[$post_type] ) ); ?>>
				<?php echo ucwords( $post_type ); ?>
			</label>
			<br>

			<?php
		}

		echo '<p class="description">Select field types to generate a PDF and have it emailed when the post is saved.</p>';
		echo '</fieldset>';

	}

	/**
	 * Sanitize our post types settings before they're saved to the database
	 *
	 * @param $input
	 * @uses sanitize_key() to sanitize our array keys
	 *
	 * @since    1.0.0
	 *
	 * @return	 array	 sanitized post types array
	 */
	public function post_types_sanitize( $input ) {

		if ( ! is_array( $input ) ) {
			$input = (array) $input;
		}

		$all_post_types = get_post_types( array( 'public' => true ) );

		foreach ( $input as $key => $value ) {
			if ( in_array( $key, $all_post_types) ) {
				// If it is a current post type
				$key = sanitize_key( $key );
			}
			else {
				// If it's not a current post type, remove it from the array
				unset( $input[$key] );
			}
		}

		return $input;

	}

	/**
	 * Register our setting with WordPress' Settings API
	 *
	 * @uses register_setting() to register our plugin setting
	 * @uses add_settings_section() to define our settings section
	 * @uses add_settings_field() to add a field to our settings section
	 *
	 * @since    1.0.0
	 */
	public function add_settings() {

		// Register email address setting
		register_setting(
			'pdf-email-settings-group',							// $option_group
			'pdf_email_address',								// $option_name
			array( $this, 'email_address_sanitize')				// $sanitize_callback
		);

		// Register post types setting
		register_setting(
			'pdf-email-settings-group',							// $option_group
			'pdf_email_post_types',								// $option_name
			array( $this, 'post_types_sanitize')				// $sanitize_callback
		);

		// Add our settings section
		add_settings_section(
			'pdf-email-settings-section',						// $id
			__( 'Plugin Options', $this->plugin_slug ),			// $title
			array( $this, 'settings_section_callback' ),		// $callback
			$this->plugin_slug									// $page
		);

		// Add a field to our settings section for email address
		add_settings_field(
			'pdf-email-address',								// $id
			__( 'Email Address', $this->plugin_slug ),			// $title
			array( $this, 'email_address_field_callback' ),		// $callback
			$this->plugin_slug,									// $page
			'pdf-email-settings-section'						// $section
		);

		// Add a field to our settings section for post type selection
		add_settings_field(
			'pdf-email-post-types',								// $id
			__( 'Post Types', $this->plugin_slug ),				// $title
			array( $this, 'post_types_field_callback' ),		// $callback
			$this->plugin_slug,									// $page
			'pdf-email-settings-section'						// $section
		);

	}

}
