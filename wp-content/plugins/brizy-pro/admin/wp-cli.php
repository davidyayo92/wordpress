<?php defined( 'ABSPATH' ) or die();

class BrizyPro_Admin_WpCli {

	public function __construct() {
		WP_CLI::add_command( 'brizy-pro license activate',   [ $this, 'activateLicense' ] );
		WP_CLI::add_command( 'brizy-pro license deactivate', [ $this, 'deactivateLicense' ] );
		WP_CLI::add_command( 'brizy-pro wl activate',        [ $this, 'whiteLabelActivate' ] );
		WP_CLI::add_command( 'brizy-pro wl deactivate',      [ $this, 'whiteLabelDeactivate' ] );
		WP_CLI::add_command( 'brizy-pro is-activated',       [ $this, 'isActivated' ] );
	}

	/**
	 * Activate Brizy Pro License key.
	 *
	 * ## OPTIONS
	 *
	 * <license-key>
	 * : A valid Brizy Pro License key.
	 *
	 * ## EXAMPLES
	 *
	 * 1. wp brizy-pro license activate myLicenseKey
	 *      - Activate license on the current site.
	 *
	 * @param array $args
	 *
	 * @throws \WP_CLI\ExitException
	 */
	public function activateLicense( array $args ) {

		WP_CLI::line( 'License activation started...' );

		try {
			WP_CLI::success( BrizyPro_Admin_License::_init()->activate( [ 'key' => $args[0] ] ) );
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Deactivate Brizy Pro License key on the current website.
	 *
	 * ## EXAMPLES
	 *
	 * - wp brizy-pro license deactivate
	 *      - Activate license on the current site.
	 *
	 *
	 * @throws \WP_CLI\ExitException
	 */
	public function deactivateLicense() {

		WP_CLI::line( 'License deactivation started...' );

		try {
			BrizyPro_Admin_License::_init()->deactivate();
			WP_CLI::success( esc_html__( 'License was successfully deactivated!', 'brizy-pro' ) );
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Check whether Brizy Pro is activated or not.
	 *
	 * ## OPTIONS
	 *
	 * [<key>]
	 * : A valid Brizy Pro License key.
	 *
	 * ## EXAMPLES
	 *
	 *     # Check whether Brizy Pro is activated; exit status 0 if activated, otherwise 1
	 *     $ wp brizy-pro is-activated
	 *     $ echo $?
	 *     1
	 *
	 *     # Bash script for checking whether Brizy Pro is activated or not
	 *     if ! wp brizy-pro is-activated; then
	 *         wp brizy-pro license activate myLicenseKey
	 *     fi
	 *
	 * - wp brizy-pro is-activated
	 *      - Check if Brizy Pro plugin is activated
	 *
	 * - wp brizy-pro is-activated keyLicenseExample
	 *      - Check if keyLicenseExample is the license activated on the current installation.
	 *
	 * @param array $args
	 *
	 * @throws \WP_CLI\ExitException
	 */
	public function isActivated( $args ) {

		$license = BrizyPro_Admin_License::_init();

		if (
			! $license->isValidLicense()
			||
			( ! empty( $args[0] ) && $args[0] !== $license->getCurrentLicense()['key'] )
		) {
			WP_CLI::halt( 1 );
			return;
		}

		WP_CLI::halt( 0 );
	}

	/**
	 * Install white label on the current website.
	 *
	 * ## OPTIONS
	 * <license>
	 * : the license supporting white label
	 *
	 * <pluginName>
	 * : the name that will be displayed in the list of plugins, update page, and menus
	 *
	 * <description>
	 * : the description that will be displayed in the list of plugins and update page
	 *
	 * <prefix>
	 * : the prefix that will be displayed in the urls example: http://localhost/wp-admin/?page=PREFIX-settings&tab=general
	 *
	 * <supportUrl>
	 * : this is the url where you can help your users
	 *
	 * <aboutUrl>
	 * : the about you/company url
	 *
	 * <logoUrl>
	 * : logo url, must be a svg url
	 *
	 * ## EXAMPLES
	 *
	 * wp brizy-pro wl activate <license> <pluginName> <description> <prefix> <supportUrl> <aboutUrl> <logoUrl>
	 *
	 * wp brizy-pro wl activate aWhiteLabelLicense "My Plugin Name" "Wp Cli plugin description " wp_cli_prefix "https://www.brizy.io/" "https://www.brizy.io/about-us" "http://brizy.local/wp-content/plugins/brizy/admin/static/img/brizy-logo.svg"
	 *
	 * @param array $args
	 *
	 * @throws \WP_CLI\ExitException
	 */
	public function whiteLabelActivate( array $args ) {

		$input = [
			'license'      => $args[0],
			'brizy'        => $args[1],
			'description'  => $args[2],
			'brizy-prefix' => $args[3],
			'support-url'  => $args[4],
			'about-url'    => $args[5],
			'brizy-logo'   => $args[6]
		];

		try {
			BrizyPro_Admin_WhiteLabel::_init()->installWhiteLabel( $input );
			WP_CLI::success( esc_html__( 'White label was successfully installed!', 'brizy-pro' ) );
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Uninstall white label on the current website.
	 *
	 * ## EXAMPLES
	 *
	 * wp brizy-pro wl deactivate
	 *
	 * @throws \WP_CLI\ExitException
	 */
	public function whiteLabelDeactivate() {
		$wl      = BrizyPro_Admin_WhiteLabel::_init();
		$license = BrizyPro_Admin_License::_init()->getCurrentLicense();

		if ( empty( $license['key'] ) ) {
			WP_CLI::error( esc_html__( 'There is no white label on this installation.', 'brizy-pro' ) );
		}

		$newWlData = [];

		foreach ( $wl->getDefaultValues() as $key => $wlValue ) {
			$newWlData[ $key ] = $wlValue->getValue();
		}

		$newWlData['license'] = $license['key'];

		try {
			BrizyPro_Admin_WhiteLabel::_init()->installWhiteLabel( $newWlData );
			WP_CLI::success( esc_html__( 'White label was successfully removed!', 'brizy-pro' ) );
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}
}