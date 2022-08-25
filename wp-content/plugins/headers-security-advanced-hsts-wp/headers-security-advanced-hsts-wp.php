<?php
/*
Plugin Name: Headers Security Advanced & HSTS WP
Plugin URI: https://www.tentacleplugins.com/
Description:  Headers Security Advanced & HSTS WP - Simple, Light and Fast. The plugin uses advanced security rules that provide huge levels of protection and it is important that your site uses it. This step is important to submit your website and/or domain to an approved HSTS list. Google officially compiles this list and it is used by Chrome, Firefox, Opera, Safari, IE11 and Edge. You can forward your site to the official HSTS preload directory. Cross Site Request Forgery (CSRF) is a common attack with the installation of Headers Security Advanced & HSTS WP will help you mitigate CSRF on your Wordpress site.
Version: 5.0.04
Text Domain: headers-security-advanced-hsts-wp
Author: 🐙  Andrea Ferro, Augusto Bombana
Author URI: https://www.linkedin.com/in/andrea-ferro-55046186/                                                                                
          __
      ___( o)>
      \ <_. )
        `---'   iron3                                                                                                                  
*/

function add_Headers_Security_Advanced_HSTS_WP_htaccess( $rules ) {
  $HEadersSecurityAdvancedServerCheckA = $_SERVER['SERVER_NAME'];
  $HEadersSecurityAdvancedCheckB = str_replace('www.','',$HEadersSecurityAdvancedServerCheckA);
  $HEadersSecurityAdvancedServerCheck3B = $_SERVER['SERVER_NAME'];
  $HEadersSecurityAdvancedCheckC03 = str_replace('.','',$HEadersSecurityAdvancedCheckB);

  $content = <<<EOD
  # Headers Security Advanced & HSTS WP - 5.0.04
  <IfModule mod_headers.c>
  Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" "expr=%{HTTPS} == 'on'"
  Header always set X-XSS-Protection "1; mode=block"
  Header always set X-Content-Type-Options "nosniff"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Expect-CT "max-age=7776000, enforce"
  Header set Access-Control-Allow-Origin "null"
  Header set Access-Control-Allow-Methods "GET,PUT,POST,DELETE"
  Header set Access-Control-Allow-Headers "Content-Type, Authorization"
  Header set X-Content-Security-Policy "img-src *; media-src * data:;"
  Header always set Content-Security-Policy "report-uri https://$HEadersSecurityAdvancedServerCheck3B"
  Header set Cross-Origin-Embedder-Policy-Report-Only 'unsafe-none; report-to="default"'
  Header set Cross-Origin-Embedder-Policy 'unsafe-none; report-to="default"'
  Header set Cross-Origin-Opener-Policy-Report-Only 'same-origin; report-to="default"'
  Header set Cross-Origin-Opener-Policy 'same-origin-allow-popups; report-to="default"'
  Header set Cross-Origin-Resource-Policy 'cross-origin'
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set Permissions-Policy "accelerometer=(), autoplay=(), camera=(), cross-origin-isolated=(), document-domain=(), encrypted-media=(), fullscreen=*, geolocation=(self), gyroscope=(), keyboard-map=(), magnetometer=(), microphone=(), midi=(), payment=*, picture-in-picture=(), publickey-credentials-get=(), screen-wake-lock=(), sync-xhr=(), usb=(), xr-spatial-tracking=(), gamepad=(), serial=(), window-placement=()"
  Header set X-Permitted-Cross-Domain-Policies "none"
  </IfModule>
  # END Headers Security Advanced & HSTS WP\n\n
  EOD;
  return $content . $rules;
}
add_filter('mod_rewrite_rules', 'add_Headers_Security_Advanced_HSTS_WP_htaccess');
  
function Headers_Security_Advanced_HSTS_WP_enable_flush_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}
register_activation_hook( __FILE__, 'Headers_Security_Advanced_HSTS_WP_enable_flush_rules' );
  
function Headers_Security_Advanced_HSTS_WP_deactivate() {
  remove_filter('mod_rewrite_rules', 'add_Headers_Security_Advanced_HSTS_WP_htaccess');
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}
register_deactivation_hook( __FILE__, 'Headers_Security_Advanced_HSTS_WP_deactivate' );
  
function Headers_Security_Advanced_HSTS_WP_widgets() {
	wp_add_dashboard_widget(
		'wpexplorer_dashboard_widget',
		'<img style="max-width:30px;" src=" data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAAF0WlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNy4xLWMwMDAgNzkuOWNjYzRkZTkzLCAyMDIyLzAzLzE0LTE0OjA3OjIyICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6cGhvdG9zaG9wPSJodHRwOi8vbnMuYWRvYmUuY29tL3Bob3Rvc2hvcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgMjMuMyAoTWFjaW50b3NoKSIgeG1wOkNyZWF0ZURhdGU9IjIwMjItMDMtMjlUMTY6Mjk6NDgrMDI6MDAiIHhtcDpNb2RpZnlEYXRlPSIyMDIyLTA0LTI4VDE3OjA2OjUyKzAyOjAwIiB4bXA6TWV0YWRhdGFEYXRlPSIyMDIyLTA0LTI4VDE3OjA2OjUyKzAyOjAwIiBkYzpmb3JtYXQ9ImltYWdlL3BuZyIgcGhvdG9zaG9wOkNvbG9yTW9kZT0iMyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo0YjcxODEyNy05ZjQ0LTRmNjItOWVmYS0xODVhYjFiMDBhNTEiIHhtcE1NOkRvY3VtZW50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" />Headers Security Advanced & HSTS WP',
		'Headers_Security_Advanced_HSTS_WP_widget_function'
	);
}
add_action( 'wp_dashboard_setup', 'Headers_Security_Advanced_HSTS_WP_widgets' );

function Headers_Security_Advanced_HSTS_WP_widget_function() {
	echo '<h2><span style="color:#0ca533;">👋 <b>Congratulations</b> you are safe,</span></h2><br><b>The Headers Security Advanced & HSTS WP</b> project implements HTTP response headers that your site can use to increase the security of your website. The plug-in will automatically set up all Best Practices (you don’t have to think about anything).<br /><br /><span style="color:#0ca533;"></span> <br />'; 
  echo '<script type="text/javascript" src="https://cdnjs.buymeacoffee.com/1.0.0/button.prod.min.js" data-name="bmc-button" data-slug="tentacleplugins" data-color="#FFDD00" data-emoji=""  data-font="Inter" data-text="Buy me a coffee" data-outline-color="#000000" data-font-color="#000000" data-coffee-color="#ffffff" ></script>';  
}

function Headers_Security_Advanced_HSTS_WP_send_header() {
  header( 'Strict-Transport-Security: max-age=63072000; includeSubDomains; preload' );
}
add_action( 'send_headers', 'Headers_Security_Advanced_HSTS_WP_send_header' );

function Headers_Security_Advanced_HSTS_WP_Headers( $headers ) {
  $HEadersSecurityAdvancedServerCheck = $_SERVER['SERVER_NAME'];
  $HEadersSecurityAdvancedCheck = str_replace('www.','',$HEadersSecurityAdvancedServerCheck);

  $HEadersSecurityAdvancedServerCheck3 = $_SERVER['SERVER_NAME'];

  $headers['X-XSS-Protection'] = '1; mode=block';
  $headers['Expect-CT'] = 'max-age=7776000, enforce';
  $headers['Access-Control-Allow-Origin'] = 'null';
  $headers['Access-Control-Allow-Methods'] = 'GET,PUT,POST,DELETE';
  $headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization';
  $headers['X-Content-Security-Policy'] = 'default-src \'self\'; img-src *; media-src * data:;';
  $headers['X-Content-Type-Options'] = 'nosniff';
  $headers['Content-Security-Policy'] = "report-uri https://$HEadersSecurityAdvancedCheck";
  $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
  $headers['Cross-Origin-Embedder-Policy-Report-Only'] = 'require-corp; report-to="default"';
  $headers['Cross-Origin-Embedder-Policy'] = 'unsafe-none; report-to="default"';
  $headers['Cross-Origin-Opener-Policy-Report-Only'] = 'same-origin; report-to="default"';
  $headers['Cross-Origin-Opener-Policy'] = 'same-origin-allow-popups; report-to="default"';
  $headers['Cross-Origin-Resource-Policy'] = 'cross-origin';
  // $headers['strict-dynamic'] = "https: 'self'; default-src 'self'";
  $headers['X-Frame-Options'] = 'SAMEORIGIN';
  $headers['Permissions-Policy'] = "accelerometer=(), autoplay=(), camera=(), cross-origin-isolated=(), document-domain=(), encrypted-media=(), fullscreen=*, geolocation=(self), gyroscope=(), keyboard-map=(), magnetometer=(), microphone=(), midi=(), payment=*, picture-in-picture=(), publickey-credentials-get=(), screen-wake-lock=(), sync-xhr=(), usb=(), xr-spatial-tracking=(), gamepad=(), serial=(), window-placement=()";
  $headers['Feature-Policy'] = "display-capture 'self'";
  $headers['X-Permitted-Cross-Domain-Policies'] = "none";  
  
  return $headers;
}
add_filter( 'wp_headers', 'Headers_Security_Advanced_HSTS_WP_Headers' );

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Headers Security Advanced & HSTS WP - VERSION
if( ! defined( 'headers-security-advanced-hsts-wp-login-version' ) ) {
	define( 'headers-security-advanced-hsts-wp-login-version', '4.3.0' );
}

// Headers Security Advanced & HSTS WP
if( ! defined( 'headers-security-advanced-hsts-wp-login-name' ) ) {
	define( 'headers-security-advanced-hsts-wp-login-name', 'Headers Security Advanced & HSTS WP' );
}

// Headers Security Advanced & HSTS WP - DIR
if ( ! defined( 'headers_security_advanced_hsts_wp_login_path' ) ) {
	define( 'headers_security_advanced_hsts_wp_login_path', plugin_dir_path( __FILE__ ) );
}

// Headers Security Advanced & HSTS WP - URI
if ( ! defined( 'headers-security-advanced-hsts-wp-base-uri' ) ) {
	define( 'headers-security-advanced-hsts-wp-base-uri', plugin_dir_url( __FILE__ ) );
}

// Headers Security Advanced & HSTS WP - MENU
add_action( 'admin_menu', 'csrf_Headers_Security_Advanced_HSTS_WP_auth' );

function csrf_Headers_Security_Advanced_HSTS_WP_auth() {
  add_options_page( 'Headers Security Advanced & HSTS WP', 'Headers Security Advanced & HSTS WP', 'manage_options', 'headers_security_advanced_hsts_wp_option_menu', 'csrf_Headers_Security_Advanced_HSTS_WP_options' );
}

function csrf_Headers_Security_Advanced_HSTS_WP_options() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
  echo '<div class="wrap">';
  echo '<h2><span style="color:#0ca533;">👋 <b>Congratulations</b> you are safe,</span></h2><br><b>The Headers Security Advanced & HSTS WP</b> project implements HTTP response headers that your site can use to increase the security of your website. <br /><br />The plug-in will automatically set up all Best Practices (you don’t have to think about anything).<br /><br />
  <br /></div></div>';
  echo '<script type="text/javascript" src="https://cdnjs.buymeacoffee.com/1.0.0/button.prod.min.js" data-name="bmc-button" data-slug="tentacleplugins" data-color="#FFDD00" data-emoji=""  data-font="Inter" data-text="Buy me a coffee" data-outline-color="#000000" data-font-color="#000000" data-coffee-color="#ffffff" ></script>';  
  echo '</div>';
}

add_filter('plugin_action_links', 'Headers_Security_Advanced_HSTS_WP_hs_links', 10, 2);
function Headers_Security_Advanced_HSTS_WP_hs_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="https://www.buymeacoffee.com/tentacleplugins">Donate a coffee</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
?>