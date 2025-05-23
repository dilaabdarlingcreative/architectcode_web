<?php
/**
 * The Mock-up to demonstrate and test module use.
 *
 * @link       https://themeisle.com
 * @since      1.0.0
 *
 * @package    Uptime_Monitor_OBFX_Module
 */

/**
 * The class defines a new module to be used by Orbit Fox plugin.
 *
 * @package    Uptime_Monitor_OBFX_Module
 * @author     Themeisle <friends@themeisle.com>
 */
class Google_Analytics_OBFX_Module extends Orbit_Fox_Module_Abstract {
	/**
	 * @var string Uptime api endpoint.
	 */
	private $api_url = 'https://analytics.orbitfox.com/api/pirate-bridge/v1';

	/**
	 * Setup module strings
	 *
	 * @access  public
	 */
	public function set_module_strings() {
		$this->name        = __( 'Analytics Integration', 'themeisle-companion' );
		$this->description = __( 'A module to integrate Google Analytics into your site easily.', 'themeisle-companion' );
	}

	/**
	 * Determine if module should be loaded.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return bool
	 */
	public function enable_module() {
		if ( ! $this->is_module_active() ) {
			return false;
		}

		$token = get_option( 'obfx_token', '' );
		if ( empty( $token ) ) {
			return false;
		}

		if ( empty( $this->get_module_setting( 'analytics_accounts_select' ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * The loading logic for the module.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function load() {
	}

	/**
	 * Method called on module activation.
	 * Calls the API to register an url to monitor.
	 *
	 * @since   2.3.3
	 * @access  public
	 */
	public function after_options_save() {
	}

	/**
	 * Method invoked before options save.
	 *
	 * @since   2.3.3
	 * @access  public
	 */
	public function before_options_save() {
		$this->deactivate();
	}

	/**
	 * Method to define hooks needed.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public function hooks() {
		$this->loader->add_action( 'rest_api_init', $this, 'register_endpoints' );
		$this->loader->add_action( 'wp_head', $this, 'output_analytics_code', 0 );
	}

	/**
	 * Register endpoint for refreshing analytics.
	 */
	public function register_endpoints() {
		register_rest_route(
			'obfx-' . $this->slug,
			'/obfx-analytics',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'refresh_tracking_links' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				),
			)
		);
	}

	/**
	 * Refresh Tracking links.
	 *
	 * @return array|bool|WP_Error
	 */
	public function refresh_tracking_links() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$obfx_token = get_option( 'obfx_token', '' );

		if ( ( $_POST['deactivate'] === 'unregister' ) ) { //phpcs:ignore WordPress.Security
			return $this->unregister_website( $obfx_token );
		}
		if ( empty( $obfx_token ) ) {
			return new WP_Error( '200', 'Your site is not registered.' );
		}

		return false;
	}

	/**
	 * Unregister website.
	 *
	 * @param $obfx_token
	 *
	 * @return array|bool|WP_Error
	 */
	public function unregister_website( $obfx_token ) {
		if ( ! isset( $obfx_token ) ) {
			return false;
		}
		delete_option( 'obfx_token' );
		delete_option( 'obfx_google_accounts_tracking_codes' );
		$req_headers = array( 'x-obfx-auth' => $obfx_token );
		$req_body    = array(
			'site_url'  => home_url(),
			'site_hash' => $this->get_site_hash(),
		);

		$request = wp_remote_post(
			$this->api_url . '/remove_website',
			array(
				'headers' => $req_headers,
				'body'    => $req_body,
			)
		);

		return $request;
	}

	/**
	 * Method that returns an array of scripts and styles to be loaded
	 * for the front end part.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return array
	 */
	public function public_enqueue() {
		return array();
	}

	/**
	 * Returns rest endpoint url.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function get_endpoint_url( $path = '' ) {
		return rest_url( 'obfx-' . $this->slug . $path );
	}

	/**
	 * Method that returns an array of scripts and styles to be loaded
	 * for the admin part.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return array|boolean
	 */
	public function admin_enqueue() {
		return array();
	}

	/**
	 * Method to define the options fields for the module
	 *
	 * @since   1.0.0
	 * @access  public
	 * @return array
	 */
	public function options() {
		$token = get_option( 'obfx_token', '' );
		if ( empty( $token ) ) {
			$url = $this->api_url . '/auth';
			$url = add_query_arg(
				array(
					'site_hash'   => $this->get_site_hash(),
					'site_url'    => home_url(),
					'site_return' => admin_url( 'admin.php?page=obfx_companion#obfx-mod-google-analytics' ),
				),
				$url
			);

			return array(
				array(
					'id'         => 'google_signin',
					'name'       => 'google_signin',
					'type'       => 'link',
					'url'        => $url,
					'link-class' => 'btn btn-success',
					'text'       => '<span class="dashicons dashicons-googleplus obfx-google"></span>' . __( 'Authenticate with Google', 'themeisle-companion' ),
				),
			);
		}

		$options = array( '-' => __( 'Select a tracking code', 'themeisle-companion' ) . '...' );

		$accounts = get_option( 'obfx_google_accounts_tracking_codes', array() );

		if ( ! empty( $accounts ) ) {
			foreach ( $accounts as $account ) {
				if ( ! isset( $account->tracking_code ) ) {
					continue;
				}
				$options[ $account->tracking_code ] = $account->account_name . ' - ' . $account->tracking_code;
			}
		}

		return array(
			array(
				'id'      => 'analytics_accounts_select',
				'name'    => 'analytics_accounts_select',
				'type'    => 'select',
				'options' => $options,
				'default' => '-',
				'title'   => 'Tracking code',
			),
			array(
				'id'            => 'analytics_accounts_unregister',
				'name'          => 'analytics_accounts_unregister',
				'type'          => 'link',
				'link-class'    => 'btn btn-sm',
				'link-id'       => 'unregister-analytics',
				'text'          => '<i class="dashicons dashicons-no"></i>' . __( 'Unregister Site', 'themeisle-companion' ),
				'url'           => '',
				'unregisterURL' => $this->get_endpoint_url( '/obfx-analytics' ),
			),
		);
	}

	/**
	 * Get tracking codes from server.
	 *
	 * @param string $obfx_token
	 * @param bool   $forced
	 *
	 * @return bool|string
	 */
	public function get_tracking_codes( $obfx_token = '', $forced = false ) {

		if ( empty( $obfx_token ) ) {
			return false;
		}

		$req_headers = array( 'x-obfx-auth' => $obfx_token );
		$req_body    = array(
			'site_url'      => home_url(),
			'site_hash'     => $this->get_site_hash(),
			'forced_update' => 'not_forced',
		);

		if ( $forced === true ) {
			$req_body['forced_update'] = 'forced_update';
		}

		$request = wp_remote_post(
			$this->api_url . '/get_tracking_links',
			array(
				'headers' => $req_headers,
				'body'    => $req_body,
			)
		);

		if ( empty( $request['body'] ) ) {
			return false;
		}
		$accounts = json_decode( $request['body'] );
		if ( empty( $accounts ) ) {
			return false;
		}

		update_option( 'obfx_google_accounts_tracking_codes', $accounts );
	}

	/**
	 * Generate a website hash.
	 *
	 * @return string
	 */
	private function get_site_hash() {
		if ( defined( 'AUTH_KEY' ) && defined( 'SECURE_AUTH_KEY' ) && defined( 'LOGGED_IN_KEY' ) ) {
			$hash_base = AUTH_KEY . SECURE_AUTH_KEY . LOGGED_IN_KEY;
		} else {
			$hash_base = sha1( ABSPATH ) . sha1( get_site_url() );
		}
		$pre_hash = rtrim( ltrim( sanitize_text_field( preg_replace( '/[^a-zA-Z0-9]/', '', $hash_base ) ) ) );
		if ( function_exists( 'mb_strimwidth' ) ) {
			return mb_strimwidth( $pre_hash, 0, 100 );
		}

		return substr( $pre_hash, 0, 100 );
	}

	final public function output_analytics_code() {
		$ua_code = $this->get_option( 'analytics_accounts_select' ); ?>
		<!-- Google Analytics -->
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ua_code ); ?>"></script>
		<script>
			window.dataLayer = window.dataLayer || [];

			function gtag() {
				dataLayer.push( arguments );
			}

			gtag( 'js', new Date() );

			gtag( 'config', '<?php echo esc_attr( $ua_code ); ?>' );
		</script>
		<!-- End Google Analytics -->
		<?php
	}
}
