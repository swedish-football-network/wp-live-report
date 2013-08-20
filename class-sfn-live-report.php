<?php
/**
 * SFN Live Report.
 *
 * @package   SFN Live Report
 * @author    Hampus Persson <hampus@hampuspersson.se>
 * @license   GPL-2.0+
 * @link      http://
 * @copyright 2013 Swedish Football Network
 */

/**
 * Plugin class.
 *
 *
 * @package SNF_Live_Report
 * @author  Hampus Persson <hampus@hampuspersson.se>
 */
class SFN_Live_Report {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.1.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'sfn-live-report';

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
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// At this point no options are needed but if the need arises this is
		// how to add the options page and menu item.
		// add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add actions to remove some unwanted scripts and styles that wp_head() forces on us
		// add_action( 'wp_print_scripts', array( $this, 'sfn_live_report_remove_scripts' ) );
		// add_action( 'wp_print_styles', array( $this, 'sfn_live_report_remove_styles' ) );

		// Setup callback for AJAX, through WPs admin-ajax
		add_action('wp_ajax_sfn-submit', array( $this, 'sfn_live_report_callback' ));

		// Add the shortcode that sets up the whole operation
		add_shortcode( 'sfn-live-report', array( $this, 'report_games' ) );
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
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		// TODO: Define activation functionality here
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), $this->version );
		}

	}

	/**
	 * Register and enqueue admin-specific JS.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/javascripts/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
		}

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/style.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueues public-facing JS files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/javascripts/main.min.js', __FILE__ ), array( 'jquery' ), $this->version );
		// Set an object with the ajaxurl for use in main JS
		wp_localize_script( $this->plugin_slug . '-plugin-script', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Since this plugin doesn't use any other plugins we can remove all scripts that are queued and registered properly
	 *
	 * @since     1.0.0
	 */
	public function sfn_live_report_remove_scripts() {
		global $wp_scripts;
		foreach( $wp_scripts->queue as $handle ) :
			if( 'jquery' != $handle && 'sfn-live-report-plugin-script' != $handle ) {
				wp_deregister_script( $handle );
				wp_dequeue_script( $handle );
			}
		endforeach;
	}

	/**
	 * Since this plugin doesn't use any other plugins we can remove all styles that are queued and registered properly
	 *
	 * @since     1.0.0
	 */
	public function sfn_live_report_remove_styles() {
		global $wp_styles;
		foreach( $wp_styles->queue as $handle ) :
			if( 'sfn-live-report-plugin-styles' != $handle ) {
				wp_deregister_style( $handle );
				wp_dequeue_style( $handle );
			}
		endforeach;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_plugins_page(
			__( 'SFN Live Report', $this->plugin_slug ),
			__( 'Settings', $this->plugin_slug ),
			'read',
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
	 * Callback for ajax call when the form is submitted
	 *
	 * @since    1.0.0
	 */
	public function sfn_live_report_callback() {

		// Check that the user has access rights
		if ( !$this->check_access()  ) {
			echo '<p>Du har inte behörighet att komma åt detta verktyg.</p>';
			echo '<h3><a href="' . wp_login_url( site_url( $_SERVER["REQUEST_URI"] ) ) . '">Logga in</a></h3>';
			die();
		}

		// If everything checks out and a game is submitted we'll update the meta
		if( $_POST['game'] ) {
			update_post_meta($_POST['game'], 'hemmares', $_POST['home']);
			update_post_meta($_POST['game'], 'bortares', $_POST['away']);
			update_post_meta($_POST['game'], 'matchtid', $_POST['time']);

			if( "true" === $_POST['sendToTwitter'] ) {
				$url = "http://www.swedishfootballnetwork.se/games/" . $_POST['game'];
				if( "true" === $_POST['finalScore'] ) {
					$time = ' (Slut)';
				} else {
					$time = empty($_POST['time']) ? '' : ' (' . $_POST['time'] . ')';
				}
				$tweet = $_POST['home-team'] . ' - ' . $_POST['away-team'] . ' ' . $_POST['home'] . '-' . $_POST['away'] . $time . '. Mer info om matchen på: ' . $url;
				$this->tweetUpdate($tweet);
			}
		}
	}

	private function tweetUpdate($tweet) {
		require_once('twitteroauth/twitteroauth.php');
		define('CONSUMER_KEY', 'VPWk1tJtrYVoTMTWTLe30A');
		define('CONSUMER_SECRET', 'EohfFFQzUSlsQnVjG9aVS1fCdxVv6GCzv6wEkTXYSi8');
		define('OAUTH_CALLBACK', 'http://example.com/twitteroauth/callback.php');

		/* TOP SECRET STUFF, DO NOT SHARE! */
		$access_token = array(
			"oauth_token" => "1080024924-NpA52OvkNMzzeJivBpHKEEenBzfVdrutsr4qJy4",
			"oauth_token_secret" => "Xuk19qsgsrlnkTr3VCLnvtwjg1yUnW1u6ykCnbOjkE"
		);

		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

		$parameters = array('status' => $tweet);
		$content = $connection->post('statuses/update', $parameters);

		return $content;
	}

	/**
	 * Function to output the form to the user
	 *
	 * @since    1.0.0
	 */
	public function report_games( $atts ) {

		// Check that the user has access rights
		if ( !$this->check_access()  ) {
			echo '<p>Du har inte behörighet att komma åt detta verktyg.</p>';
			echo '<h3><a href="' . wp_login_url( site_url( $_SERVER["REQUEST_URI"] ) ) . '">Logga in</a></h3>';
			die();
		}

		// Store the output we want in a variable
		$return_page = include('views/public.php');

		return $return_page;
	}

	private function check_access() {
		$allowed_users = array(
			6, 		// Hampus Persson, SFN
			373 	// Carl Klimfors, LG
		);
		$current_user = wp_get_current_user();
		if ( in_array($current_user->ID, $allowed_users) ) {
			return true;
		}
		return false;
	}
}