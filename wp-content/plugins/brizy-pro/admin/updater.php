<?php defined( 'ABSPATH' ) or die();

class BrizyPro_Admin_Updater {

	private $api_url;
	private $api_data;
	private $slug;
	private $wp_override = false;
	private $cache_key = '';

	/**
	 * @return BrizyPro_Admin_Updater|mixed
	 */
	public static function _init() {

		static $instance;

		return $instance ? $instance : $instance = new self();
	}

	/**
	 * @throws Exception
	 */
	public function __construct() {

		$this->api_url  = trailingslashit( BrizyPro_Config::UDPATE_LICENSE );
		$this->slug     = basename( BRIZY_PRO_PLUGIN_BASE, '.php' );
		$this->api_data = Brizy_Editor_Project::get()->getMetaValue( BrizyPro_Admin_License::LICENSE_META_KEY );

		if ( ! $this->api_data ) {
			$this->api_data = BrizyPro_Config::getLicenseActivationData();
		}

		$this->api_data['version'] = BRIZY_PRO_VERSION;

		//$this->wp_override = isset( $_api_data['wp_override'] ) ? (bool) $_api_data['wp_override'] : false;
		$this->cache_key = 'brizy_update__transient_' . md5( serialize( $this->slug . ( isset( $this->api_data['key'] ) ? $this->api_data['key'] : '' ) ) );

		$this->hooks();
		$this->maybe_delete_transients();
	}

	/**
	 * Set up WordPress filters to hook into WP's update process.
	 *
	 * @return void
	 * @uses add_filter()
	 *
	 */
	public function hooks() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_action( 'delete_site_transient_update_plugins', array( $this, 'delete_transients' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
		add_filter( 'upgrader_pre_download', array( $this, 'upgrader_pre_download' ), 10, 3 );
		//add_action( 'admin_init', array( $this, 'show_changelog' ) );
	}

	public function delete_transients() {
		$this->delete_transient( $this->cache_key );
	}

	private function maybe_delete_transients() {
		global $pagenow;

		if ( isset( $_GET['force-check'] ) && 'update-core.php' === $pagenow ) {
			$this->delete_transients();
		}
	}

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update API just when WordPress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native WordPress plugin update code.
	 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
	 *
	 * @param array $_transient_data Update array build by WordPress.
	 *
	 * @return array Modified update array with custom plugin data.
	 * @uses api_request()
	 *
	 */
	public function check_update( $_transient_data ) {

		global $pagenow;

 		if ( ! is_object( $_transient_data ) ) {
			$_transient_data = new stdClass;
		}

		if ( 'plugins.php' == $pagenow && is_multisite() ) {
			return $_transient_data;
		}

		$version_info = $this->get_cached_version_info();

		if ( isset( $_transient_data->response[ BRIZY_PLUGIN_BASE ] ) && ! isset( $_transient_data->response[ BRIZY_PRO_PLUGIN_BASE ] ) ) {
			$version_info = false;
		}

		if ( false === $version_info ) {
			$version_info = $this->api_request();
			$this->set_version_info_cache( $version_info );
		}

		if ( is_wp_error( $version_info ) || empty( $version_info['new_version'] ) ) {
			return $_transient_data;
		}

		$version_info = (object) $version_info;

		if ( version_compare( BRIZY_PRO_VERSION, $version_info->new_version, '<' ) ) {
			$_transient_data->response[ BRIZY_PRO_PLUGIN_BASE ] = $version_info;
			$_transient_data->checked[ BRIZY_PRO_PLUGIN_BASE ]  = $version_info->new_version;
		} else {
			$_transient_data->no_update[ BRIZY_PRO_PLUGIN_BASE ] = $version_info;
			$_transient_data->checked[ BRIZY_PRO_PLUGIN_BASE ]   = BRIZY_PRO_VERSION;
		}

		$_transient_data->last_checked = time();

		return $_transient_data;
	}


	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @param mixed $_data
	 * @param string $_action
	 * @param object $_args
	 *
	 * @return object $_data
	 * @uses api_request()
	 *
	 */
	public function plugins_api_filter( $_data, $_action = '', $_args = null ) {

		if ( $_action != 'plugin_information' ) {
			return $_data;
		}

		if ( ! isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) {
			return $_data;
		}

		$cache_key = 'brizy_api_request_' . md5( serialize( $this->slug . $this->api_data['key'] ) );

		// Get the transient where we store the api request for this plugin for 24 hours
		$brizy_api_request_transient = $this->get_cached_version_info( $cache_key );

		//If we have no transient-saved value, run the API, set a fresh transient with the API value, and return that value too right now.
		if ( empty( $brizy_api_request_transient ) ) {

			$api_response = $this->api_request();

			// Expires in 9 hours
			$this->set_version_info_cache( $api_response, $cache_key );

			if ( false !== $api_response ) {
				$_data = $api_response;
			}

		} else {
			$_data = $brizy_api_request_transient;
		}

		$api_request_transient = new \stdClass();

		$api_request_transient->name          = $_data['name'];
		$api_request_transient->slug          = $this->slug;
		$api_request_transient->author        = '<a href="https://brizy.io/">Brizy.io</a>';
		$api_request_transient->homepage      = 'https://brizy.io/';
		$api_request_transient->requires      = $_data['requires'];
		$api_request_transient->requires_php  = $_data['requires_php'];
		$api_request_transient->tested        = $_data['tested'];
		$api_request_transient->version       = $_data['new_version'];
		$api_request_transient->download_link = $_data['download_link'];
		$api_request_transient->banners       = [
			'high' => 'https://ps.w.org/brizy/assets/banner-1544x500.jpg',
			'low'  => 'https://ps.w.org/brizy/assets/banner-772x250.jpg',
		];
		$api_request_transient->sections      = unserialize( $_data['sections'] );
		$_data                                = $api_request_transient;

		return $_data;
	}

	function upgrader_pre_download( $reply, $package, $upgrader ) {

		if ( strpos( $package, 'brizy.io/account/misc/brizy-license' ) === false ) {
			return $reply;
		}
		$version_info = $this->get_cached_version_info();

		if ( ! empty( $version_info['error']['message'] ) ) {
			$reply                  = new WP_Error( 'BrizyPRO_ERROR', $version_info['error']['message'] );
			$upgrader->result       = null;
			$upgrader->skin->result = $reply;
		}

		return $reply;
	}

	/**
	 * Disable SSL verification in order to prevent download update failures
	 *
	 * @param array $args
	 * @param string $url
	 *
	 * @return object $array
	 */
	public function http_request_args( $args, $url ) {

		$verify_ssl = $this->verify_ssl();
		if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'brizy_action=package_download' ) ) {
			$args['sslverify'] = $verify_ssl;
		}

		return $args;

	}

	/**
	 * Calls the API and, if successfull, returns the object delivered by the API.
	 *
	 * @return false|object
	 */
	protected function api_request() {

		$data = $this->api_data;

		if ( $this->api_url == trailingslashit( home_url() ) ) {
			return false; // Don't allow a plugin to ping itself
		}

		$api_params = [
			'key'             => ! empty( $data['key'] ) ? $data['key'] : '',
			'item_name'       => ! empty( $data['item_name'] ) ? $data['item_name'] : '',
			'theme_id'        => ! empty( $data['theme_id'] ) ? $data['theme_id'] : '',
			'version'         => ! empty( $data['version'] ) ? $data['version'] : '',
			'market'          => ! empty( $data['market'] ) ? $data['market'] : '',
			'author'          => ! empty( $data['author'] ) ? $data['author'] : '',
			'slug'            => $this->slug,
			'request[domain]' => home_url()
		];

		$verify_ssl = $this->verify_ssl();
		$request    = wp_remote_post( $this->api_url, [
			'timeout'   => 60,
			'sslverify' => $verify_ssl,
			'body'      => $api_params
		] );

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		$responseCode = wp_remote_retrieve_response_code( $request );
		if ( 200 !== (int) $responseCode ) {
			return new WP_Error( $responseCode, sprintf( esc_html__( 'On last check of the license status a HTTP error occured: server response code is %s. Please try to force updates in the update page of the dashboard.', 'brizy-pro' ), $responseCode ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $request ), true );
		if ( empty( $data ) || ! is_array( $data ) ) {
			return new WP_Error( 'no_json', esc_html__( 'An error occurred on json decode response, please try to force updates in the update page of the dashboard.', 'brizy-pro' ) );
		}

		$data['plugin'] = BRIZY_PRO_PLUGIN_BASE;

		return $data;
	}

	protected function show_changelog() {

		global $edd_plugin_data;

		if ( empty( $_REQUEST['brizy_action'] ) || 'view_plugin_changelog' != $_REQUEST['brizy_action'] ) {
			return;
		}

		if ( empty( $_REQUEST['plugin'] ) ) {
			return;
		}

		if ( empty( $_REQUEST['slug'] ) ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_die( __( 'You do not have permission to install plugin updates', 'brizy-pro' ), __( 'Error', 'brizy-pro' ), array( 'response' => 403 ) );
		}

		$data         = $edd_plugin_data[ $_REQUEST['slug'] ];
		$beta         = ! empty( $data['beta'] ) ? true : false;
		$cache_key    = md5( 'brizy_plugin_' . sanitize_key( $_REQUEST['plugin'] ) . '_' . $beta . '_version_info' );
		$version_info = $this->get_cached_version_info( $cache_key );

		if ( false === $version_info ) {

			$api_params = array(
				'brizy_action' => 'get_version',
				'item_name'    => isset( $data['item_name'] ) ? $data['item_name'] : false,
				'item_id'      => isset( $data['item_id'] ) ? $data['item_id'] : false,
				'slug'         => $_REQUEST['slug'],
				'author'       => $data['author'],
				'url'          => home_url()
			);

			$verify_ssl = $this->verify_ssl();
			$request    = wp_safe_remote_post( $this->api_url, array(
				'timeout'   => 60,
				'sslverify' => $verify_ssl,
				'body'      => $api_params
			) );

			if ( is_wp_error( $request ) ) {
				Brizy_Admin_Flash::instance()->add_error( $request->get_error_message() );
				Brizy_Logger::instance()->critical( 'Updated exception', [ $request ] );
			}

			if ( ! is_wp_error( $request ) ) {
				$version_info = json_decode( wp_remote_retrieve_body( $request ) );
			}


			if ( ! empty( $version_info ) && isset( $version_info->sections ) ) {
				$version_info->sections = maybe_unserialize( $version_info->sections );
			} else {
				$version_info = false;
			}

			if ( ! empty( $version_info ) ) {
				foreach ( $version_info->sections as $key => $section ) {
					$version_info->$key = (array) $section;
				}
			}

			$this->set_version_info_cache( $version_info, $cache_key );

		}

		if ( ! empty( $version_info ) && isset( $version_info->sections['changelog'] ) ) {
			echo '<div style="background:#fff;padding:10px;">' . $version_info->sections['changelog'] . '</div>';
		}

		exit;
	}

	public function get_cached_version_info( $cache_key = '' ) {

		if ( empty( $cache_key ) ) {
			$cache_key = $this->cache_key;
		}

		$cache = get_option( $cache_key );

		if ( empty( $cache['timeout'] ) || time() > $cache['timeout'] ) {
			return false; // Cache is expired
		}

        if ( is_wp_error( $cache['value'] ) ) {
            return $cache['value'];
        }

		// We need to turn the icons into an array, thanks to WP Core forcing these into an object at some point.
		$cache['value'] = json_decode( $cache['value'], true );
		if ( ! empty( $cache['value']->icons ) ) {
			$cache['value']->icons = (array) $cache['value']->icons;
		}

		return $cache['value'];
	}

	protected function set_version_info_cache( $value = '', $cache_key = '' ) {

		if ( empty( $cache_key ) ) {
			$cache_key = $this->cache_key;
		}

		$data = array(
			'timeout' => strtotime( '+9 hours', time() ),
			'value'   => is_wp_error( $value ) ? $value : json_encode( $value )
		);

		update_option( $cache_key, $data, 'no' );
	}

	protected function delete_transient( $cache_key ) {
		delete_option( $cache_key );
	}

	/**
	 * Returns if the SSL of the store should be verified.
	 *
	 * @return bool
	 */
	private function verify_ssl() {
		return (bool) apply_filters( 'brizy_api_request_verify_ssl', true, $this );
	}
}
