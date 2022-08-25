<?php

class BrizyPro_Admin_License
{
    const LICENSE_META_KEY = 'brizy-license-key';

    /**
     * @return BrizyPro_Admin_License
     */
    public static function _init()
    {
        static $instance;

        return $instance ? $instance : $instance = new self();
    }

	/**
	 * @throws Exception
	 */
    private function __construct()
    {
	    if ( BrizyPro_Admin_WhiteLabel::_init()->getEnabled() && get_transient( BrizyPro_Admin_WhiteLabel::WL_SESSION_KEY ) != 1 ) {
		    return;
	    }

        add_action('brizy_settings_tabs',               [ $this, 'addLicenseTab' ], 10, 2);
        add_action('brizy_settings_render_tab',         [ $this, 'renderLicenseTab' ], 10, 2);
        add_action('brizy_network_settings_tabs',       [ $this, 'addLicenseTab' ], 10, 2);
        add_action('brizy_network_settings_render_tab', [ $this, 'renderLicenseTab' ], 10, 2);
        add_action('network_admin_menu',                [ $this, 'actionRegisterSubMenuLicensePage' ], 9);
        add_action( 'admin_init',                       [ $this, 'handleSubmit' ] );
        add_action( 'admin_notices',                    [ $this, 'oneSiteLicenseNotice' ] );
        add_action( 'admin_notices',                    [ $this, 'noLicenseNotice' ] );
        add_action( 'admin_notices',                    [ $this, 'apiResponseNotice' ] );
    }

	public function getCurrentLicense()
	{
		$this->switchBlog( true );

		try {
			$licenseData = Brizy_Editor_Project::get()->getMetaValue( self::LICENSE_META_KEY );
		} catch (Exception $e) {
			$this->switchBlog( false );
			return [];
		}

		$this->switchBlog( false );

		return $licenseData;
	}

    public function handleSubmit() {

	    if ( empty( $_POST ) || ! isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] != 'license' ) {
		    return;
	    }

	    if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'validate-license' ) ) {
		    return;
	    }

        $activate = $_REQUEST['license_form_action'] == 'activate';

	    if ( is_multisite() && $activate && $this->isOneSiteLicense( $_POST['key'] ) ) {
		    Brizy_Admin_Flash::instance()->add_error( esc_html__( 'Sorry, you can’t use the Brizy Personal license in a multisite network.', 'brizy-pro' ) );
		    return;
	    }

        try {
            if ( $activate ) {
	            Brizy_Admin_Flash::instance()->add_success( $this->activate( [ 'key' => $_POST['key'] ] ) );
            } else {
                $this->deactivate();
	            Brizy_Admin_Flash::instance()->add_success( esc_html__( 'License was successfully deactivated!', 'brizy-pro' ) );
            }

        } catch ( Exception $e ) {
	        Brizy_Admin_Flash::instance()->add_error( $e->getMessage() );
        }
    }

    /**
     * @internal
     */
    public function actionRegisterSubMenuLicensePage()
    {
	    add_submenu_page(
		    Brizy_Admin_NetworkSettings::menu_slug(),
		    __( 'License', 'brizy-pro' ),
		    __( 'License', 'brizy-pro' ),
		    'manage_network',
		    Brizy_Admin_NetworkSettings::menu_slug(),
		    [ $this, 'render' ]
	    );
    }

	public function renderLicenseTab( $content = '', $tab = '' ) {
		if ( 'license' !== $tab ) {
			return $content;
		}

		$licenseData = $this->getCurrentLicense();

		if ( is_null( $licenseData ) ) {
			$licenseData = [];
		}

		// prepare license
		$key = isset( $licenseData['key'] ) ? $licenseData['key'] : null;
		if ( $key ) {
			$l = strlen( $licenseData['key'] );
			$t = str_repeat( '*', $l - 6 );
			$key = substr( $licenseData['key'], 0, 3 ) . $t . substr( $licenseData['key'], $l - 3, 3 );
		}

		$context = [
			'nonce'               => wp_nonce_field( 'validate-license', '_wpnonce', true, false ),
			'action'              => $this->getTabUrl(),
			'submit_label'        => $licenseData ? esc_html__( 'Deactivate', 'brizy-pro' ) : __( 'Activate', 'brizy-pro' ),
			'license_form_action' => $licenseData ? 'deactivate' : 'activate',
			'license'             => $key,
		];

		return Brizy_TwigEngine::instance( BRIZY_PRO_PLUGIN_PATH . "/admin/views/" )->render( 'license.html.twig', $context );
	}

	public function addLicenseTab( $tabs = '', $selected_tab = '' ) {
		if ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) {
			$tabs[] = [
				'id'          => 'license',
				'label'       => __( 'License', 'brizy-pro' ),
				'is_selected' => $selected_tab == 'license',
				'href'        => $this->getTabUrl(),
			];
		}

		return $tabs;
	}

	private function getTabUrl() {

		if ( is_multisite() ) {
			return network_admin_url( 'admin.php?page=' . Brizy_Admin_NetworkSettings::menu_slug(), false ) . '&tab=license';
		} else {
			return menu_page_url(
				       is_network_admin() ? Brizy_Admin_NetworkSettings::menu_slug() : Brizy_Admin_Settings::menu_slug(),
				       false
			       ) . '&tab=license';
		}

	}

	public function oneSiteLicenseNotice() {
		$license = $this->getCurrentLicense();

		if ( ! is_multisite() || empty( $license['key'] ) ) {
			return;
		}

		if ( ! $this->isOneSiteLicense( $license['key'] ) ) {
			return;
		}

		printf(
			'<div class="%1$s"><p>%2$s: %3$s</p></div>',
			'notice notice-error',
			BrizyPro_Admin_WhiteLabel::_init()->gettext_domain( 'Brizy Pro', 'Brizy Pro' ),
			esc_html__( 'You can\'t use a One Site License on a multisite installation.', 'brizy-pro' )
		);
	}

	public function noLicenseNotice() {
		$license = $this->getCurrentLicense();

		if ( ! empty( $license['key'] ) ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible">
            <h3><?php esc_html_e( 'Welcome to Brizy Pro!', 'brizy-pro' ) ?></h3>
            <p>
				<?php esc_html_e( 'Please activate your license to get feature updates, premium support and unlimited access to the template library.', 'brizy-pro' ) ?>
            </p>
            <a style="margin-bottom:10px" href="<?php echo esc_url( apply_filters('brizy_upgrade_to_pro_url', Brizy_Config::UPGRADE_TO_PRO_URL) ) ?>" class="button button-primary button-large"><?php esc_html_e( 'Activate', 'brizy-pro' ) ?></a>
		</div>
		<?php
	}

	/**
	 * @param $args
	 *
	 * @return string
	 * @throws Exception
	 */
	public function activate( $args ) {

        if ( empty( $args['key'] ) ) {
            throw new Exception( esc_html__( 'Please provide a license key.', 'brizy-pro' ) );
        }

		$response = $this->request( $args, BrizyPro_Config::ACTIVATE_LICENSE );

        if ( $response['code'] != 'ok' ) {
	        throw new Exception( $response['message'] );
        }

		$this->switchBlog( true );

		$licenseData        = BrizyPro_Config::getLicenseActivationData();
        $licenseData['key'] = $args['key'];

		BrizyPro_Admin_Updater::_init()->delete_transients();

		try {
			Brizy_Editor_Project::get()->setMetaValue( self::LICENSE_META_KEY, $licenseData );
			Brizy_Editor_Project::get()->saveStorage();
		} catch (Exception $e) {
			$this->switchBlog( false );
            throw $e;
		}

		$this->switchBlog( false );

		return esc_html__( 'License successfully activated.', 'brizy-pro' );

    }

	/**
	 * @param $args
	 *
     * @return string
	 * @throws Exception
	 */
	public function deactivate( $args = [] ) {

		if ( empty( $args['key'] ) ) {
			$license = $this->getCurrentLicense();

			if ( empty( $license['key'] ) ) {
				throw new Exception( esc_html__( 'No license was found in your installation', 'brizy-pro' ) );
			}

			$args['key'] = $license['key'];
		}

		// No reason to check by response key 'code', we do not know all of them: ok, no_activation_found, no_reactivation_allowed, license_not_found, etc.
		$response = $this->request( $args, BrizyPro_Config::DEACTIVATE_LICENSE );

		$this->switchBlog( true );

		BrizyPro_Admin_Updater::_init()->delete_transients();

		try {
			Brizy_Editor_Project::get()->removeMetaValue( self::LICENSE_META_KEY );
			Brizy_Editor_Project::get()->saveStorage();
		} catch (Exception $e) {
			$this->switchBlog( false );
			throw $e;
		}

		$this->switchBlog( false );

		return $response['message'];
	}

    private function switchBlog( $switch ) {

	    if ( ! is_multisite() ) {
		   return;
	    }

        if ( $switch ) {
	        switch_to_blog( get_main_site_id() );
        } else {
	        restore_current_blog();
        }

	    Brizy_Editor_Project::cleanClassCache();
    }

	/**
	 * @return array
	 * @throws Exception
	 */
	public function request( $args, $url ) {

		$defaults = [
			'key'             => '',
			'version'         => BRIZY_PRO_VERSION,
			'slug'            => basename( BRIZY_PRO_PLUGIN_BASE, '.php' ),
			'request[domain]' => home_url()
		];

		$defaults = wp_parse_args( BrizyPro_Config::getLicenseActivationData(), $defaults );
		$args     = wp_parse_args( $args, $defaults );

        if ( empty( $args['key'] ) ) {
	        throw new Exception( esc_html__( 'Please provide license key.', 'brizy-pro' ) );
        }

		$response = wp_remote_post( $url, [
			'timeout' => 60,
			'body'    => $args
		] );

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		$responseCode = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $responseCode ) {
			throw new Exception( sprintf( esc_html__( 'The remote server response code is %s.', 'brizy-pro' ), $responseCode ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data ) || ! is_array( $data ) ) {
            if ( $jsonLastError = json_last_error() ) {
	            throw new Exception( sprintf( esc_html__( 'An error occurred on json decode response. The json last error is: %s', 'brizy-pro' ), $jsonLastError ) );
            } else {
	            throw new Exception( esc_html__( 'Empty body was returned by the remote server.', 'brizy-pro' ) );
            }
		}

        if ( empty( $data['code'] ) ) {
	        throw new Exception( esc_html__( 'The response of the remote server has an unexpected format', 'brizy-pro' ) );
        }

		return $data;
	}

	public function apiResponseNotice() {
		// If no license skip this, an admin notice of no license will throw noLicenseNotice
		if ( ! BrizyPro_Admin_License::_init()->getCurrentLicense() ) {
			return;
		}

		$lastCheck = BrizyPro_Admin_Updater::_init()->get_cached_version_info();

		if ( ! is_wp_error( $lastCheck ) && ! empty( $lastCheck['error']['message'] ) ) {
			printf(
				'<div class="%1$s"><p>%2$s: %3$s</p></div>',
				'notice notice-error',
				BrizyPro_Admin_WhiteLabel::_init()->gettext_domain( 'Brizy Pro', 'Brizy Pro' ),
				$lastCheck['error']['message']
			);
		}
	}

	private function isOneSiteLicense( $licenseKey ) {
		if ( preg_match( "/^(BPNW|BZS|BFP)/i", $licenseKey ) ) {
			return true;
		}

		return false;
	}

	public function isValidLicense()
    {
		$license = $this->getCurrentLicense();

	    if ( empty( $license['key'] ) ) {
		    return false;
	    }

	    $lastCheck = BrizyPro_Admin_Updater::_init()->get_cached_version_info();

	    if ( ! is_wp_error( $lastCheck ) && ! empty( $lastCheck['error']['message'] ) ) {
		    return false;
	    }

		if ( is_multisite() && $this->isOneSiteLicense( $license['key'] ) ) {
			return false;
		}

        return true;
    }
}
