<?php
// read the options
if (isset($_POST['co_reset_options'])) {
    _co_reset_plugin_options();
}

// Function to get the Remaining Credits Of the user
function co_get_credits_info() {
    $url = CLEAROUT_GET_AVAILABLE_CREDITS_API_URL;
    $clearout_options = get_option('clearout_email_validator');
    $api_key = $clearout_options['api_key'];
    $args = array('method' => 'GET', 'data_format' => 'body', 'headers' => array('Content-Type' => 'application/json', 'Authorization' => 'Bearer:' . str_replace(' ', '', $api_key)));
    $avl_credits_info = NULL;

    $response = wp_remote_get($url, $args);
    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        // Decode the return json results and return the data.
        $body_data = json_decode($body, true);
        if($body_data['status'] == 'success') {
            $avl_credits_info = $body_data['data'];
            if(is_null($avl_credits_info['available_credits'])) {
                return NULL; // In case of Member accts, they have NULL remaining credits
            }
        }
    }
    return $avl_credits_info;
}

// Method to determine if its low credit
function co_is_low_credits ($remaining_credits_data) {
    $is_low_credit = FALSE;
    if ($remaining_credits_data['available_credits'] < $remaining_credits_data['low_credit_balance_min_threshold']) {
        $is_low_credit = TRUE;
    }
    return $is_low_credit;
}

// Method to determine if its zero credit
function co_is_zero_credits ($remaining_credits_data) {
    $is_zero_credit = FALSE;
    if ($remaining_credits_data['available_credits'] == 0) {
        $is_zero_credit = TRUE;
    }
    return $is_zero_credit;
}


//
// Action handlers
// To notify if Clearout API token not available
function co_action_admin_notice() {
    $options = get_option('clearout_email_validator');
    if (!isset($options['api_key']) || $options['api_key'] == '' || $options['api_key'] == ' ') {
        echo '<div class="notice notice-warning is-dismissible">
            <p>Please get your Clearout API Token from 
                <a href="https://app.clearout.io/dashboard/account/api" target="_blank">here</a> and save in <a href="options-general.php?page=clearout-email-validator">setting page</a>.
            </p>
		</div>';
        return;
    }

    // Control Comes here only if api key is present
    // Make API call For checking Low credit notification
    $remaining_credits_data = co_get_credits_info();
    
    if($remaining_credits_data) {
        $zero_credit_notification = co_is_zero_credits($remaining_credits_data);
        if ($zero_credit_notification) {
            echo '<div class="error notice">
                    <p>Your Clearout account has ran out of credits, <a href="https://app.clearout.io/dashboard/account/pricing" target="_blank">Buy credits</a> to eliminate all problematic email addresses.</p>
                </div>';
            return; // If its zero credits, no need to show low cred alert
        }

        $credits_threshold = $remaining_credits_data['low_credit_balance_min_threshold'];
        $low_credit_notification = co_is_low_credits($remaining_credits_data);
        if ($low_credit_notification) {
            echo '<div class="notice notice-warning">
                    <p>
                    Your Clearout account credit balance has dropped below <a href="https://app.clearout.io/dashboard/account/profile" target="_blank">' . $credits_threshold . ' credit</a> threshold.
                    </p>
                </div>';
        }        
    }   
}

function co_action_update_user_plugin_settings_change($old_value, $new_value)
{
    if ($new_value !== $old_value && !empty($new_value)) {

        $clearout_options = get_option('clearout_email_validator');
        $url = CLEAROUT_PLUGIN_SETTINGS_API_URL;

        $args = array('method' => 'POST', 'data_format' => 'body', 'headers' => array('Content-Type' => 'application/json',
            'Authorization' => 'Bearer:' . str_replace(' ', '', $clearout_options['api_key'])),
            'body' => json_encode(array('co_wp_plugin_version' => CLEAROUT_PLUGIN_VERSION, 'new_settings' => $new_value, 'old_settings' => $old_value, 'site' => get_site_url())));
        wp_remote_post($url, $args);
    }
}

function co_action_plugin_setup() {
    add_options_page('Clearout Email Validator', 'Clearout Email Validator', 'manage_options', 'clearout-email-validator', '_co_option_plugin_page');
    wp_register_script('my_clearout_plugin_script', plugins_url('../assets/js/clearout_plugin.js', __FILE__), array('jquery'), CLEAROUT_PLUGIN_VERSION, true);
    wp_localize_script('my_clearout_plugin_script', 'clearout_plugin_ajax_call', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_script('my_clearout_plugin_script');
    wp_register_style('Font_Awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('Font_Awesome');
    wp_register_style('clearout_css', plugins_url('clearout-email-validator/assets/css/clearout_plugin.css'),'',CLEAROUT_PLUGIN_VERSION);
    wp_enqueue_style('clearout_css');
}

// add the admin settings and such
function co_action_plugin_admin_init() {
    register_setting('clearout_email_validator', 'clearout_email_validator');
    add_settings_section('clearout_plugin_main', _co_ps_text_setting(), '_co_plugin_api_section_text_setting', 'co_plugin', array('class' => 'clearout_ps'));
    add_settings_section('clearout_plugin_main', '', '_co_plugin_verification_type_section_text_setting', 'co_plugin_vt');
    add_settings_section('clearout_plugin_main', '', '_co_plugin_form_section_text_setting', 'co_plugin_fs');
    add_settings_section('clearout_plugin_main', '', '', 'co_plugin_ts');
    add_settings_field('clearout_api_key', _co_api_key_label_setting(), '_co_api_key_setting', 'co_plugin', 'clearout_plugin_main', array('class' => 'clearout_api_lable'));
    add_settings_field('clearout_role_email_option', _co_rolebased_label_setting(), '_co_role_email_setting_option', 'co_plugin_vt', 'clearout_plugin_main');
    add_settings_field('clearout_disposable_option', _co_dispbased_label_setting(), '_co_disposable_setting_option', 'co_plugin_vt', 'clearout_plugin_main');
    add_settings_field('clearout_gibberish_option', _co_gibberishbased_label_setting(), '_co_gibberish_setting_option', 'co_plugin_vt', 'clearout_plugin_main');
    add_settings_field('clearout_free_option', _co_freebased_label_setting(), '_co_free_setting_option', 'co_plugin_vt', 'clearout_plugin_main',array('class' => 'clearout_lables'));
    add_settings_field('clearout_form_select_option', _co_sforms_label_setting(), '_co_form_select_setting_option', 'co_plugin_fs', 'clearout_plugin_main');
    add_settings_field('clearout_hook_select_option', _co_shooks_label_setting(), '_co_hook_select_setting_option', 'co_plugin_fs', 'clearout_plugin_main');
    add_settings_field('clearout_timeout', _co_timeout_label_setting(), '_co_timeout_setting', 'co_plugin_ts', 'clearout_plugin_main');
    add_settings_field('clearout_invalid_error_msg', _co_custom_invalid_error_label_setting(), '_co_custom_invalid_error_setting', 'co_plugin_ts', 'clearout_plugin_main');
}

//
// Hook handlers 
function co_hook_plugin_activate()
{
	$options = get_option('clearout_email_validator');
    $all_plugins = get_plugins();
    // check woocommerce installed or not
    if (array_key_exists('woocommerce/woocommerce.php', $all_plugins)) {
        if (!$options['ise_on_off']) {
            $options['ise_on_off'] = 'off';
        }
    } else {
        if (!$options['ise_on_off']) {
            $options['ise_on_off'] = 'on';
        }
    }
    update_option('clearout_email_validator', $options);

    // Send API call to co server to say plugin has been activated ONLY if secret key has been saved
    // will NOT trigger for first install -> activate
    // will trigger, when user has a api-key saved, and then activates plugins ( i.e deactivate => reactivate )
    if ($options['api_key']) {
        $url = CLEAROUT_PLUGIN_ACTIVATED_API_URL;
        $args = array('method' => 'POST', 'data_format' => 'body', 'headers' => array('Content-Type' => 'application/json',
            'Authorization' => 'Bearer:' . str_replace(' ', '', $options['api_key'])),
            'body' => json_encode(array('co_wp_plugin_version' => CLEAROUT_PLUGIN_VERSION, 'site' => get_site_url())));
        wp_remote_post($url, $args);
    }
}

function co_hook_plugin_deactivate()
{
    $clearout_options = get_option('clearout_email_validator');
    if ($clearout_options['api_key']) {
        $url = CLEAROUT_PLUGIN_DEACTIVATED_API_URL;

        $args = array('method' => 'POST', 'data_format' => 'body', 'headers' => array('Content-Type' => 'application/json',
            'Authorization' => 'Bearer:' . str_replace(' ', '', $clearout_options['api_key'])),
            'body' => json_encode(array('co_wp_plugin_version' => CLEAROUT_PLUGIN_VERSION, 'site' => get_site_url())));
        wp_remote_post($url, $args);
    }
}

// Option handlers
// display the admin options page
function _co_option_plugin_page() {
?>
	<h2 style="font-size: 1.5em;float:left;">Clearout Email Validator </h2>
    <div style="float: left;padding: 0px 20px 0px 0px;">
    <div style="background-color: #fff;padding:5px 30px 5px 30px;">
        <a href="https://clearout.io" target="_blank"><img style="float:right;margin:1em 0;" width="220" src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/clearout_wp_logo.png'; ?>" /></a>
        <p style="font-size: 14px;">Clearout Email Validator plugin seamlessly integrated with all major forms to validate the user given email address in real-time. This plugin will perform <a href="https://clearout.io/#validation_checks" target="_blank">20+ refined validation</a> checks to determine the status of email address, this would help  the email address capture process  </p>
        <ul style="list-style-type: disc;padding-left: 10px;font-size: 14px;">
            <li>To accept only valid email address </li>
            <li>To accept only business/work email address</li>
            <li>To prevent all fraudulent signups</li>
            <!-- <li><a href="https://developer.wordpress.org/plugins/Clearout-email-validator" target="_blank">Know more</a></li> -->
        </ul>
    </div>
    <br/>
    <br/>

    <div style="background-color: #ffffff61;padding:15px 30px 15px 30px;">
        <form id="clearout_setting_form" action="options.php" method="post">
        <?php settings_fields('clearout_email_validator'); ?>
        <?php do_settings_sections('co_plugin'); ?>
        <hr class="co_sec_diff"/>
        <h2 class="co_plugin_subhead">Valid Email Address</h2>
        <?php do_settings_sections('co_plugin_vt'); ?>
        <hr class="co_sec_diff"/>
        <h2 class="co_plugin_subhead">Apply Validation</h2>
        <?php do_settings_sections('co_plugin_fs'); ?>
        <hr class="co_sec_diff"/>
        <?php do_settings_sections('co_plugin_ts'); ?>
        <input name="clearout_submit" type="submit" value="<?php esc_attr_e('Apply'); ?>" class="button button-primary"/>
        <input name="clearout_reset_clicked" type="button" value="<?php esc_attr_e('Reset'); ?>" onclick="_co_reset_plugin()" class="button button-primary" style="margin-left: 10px"/>
        </form>
        <form id="clearout_reset_form" name="reset" action="options-general.php?page=clearout-email-validator" method="post">
        <input id="co_reset_options" name="co_reset_options" type="submit" style="display:none;" value="<?php esc_attr_e('Reset'); ?>" class="button button-primary"/>
        
        </form>
        <script>
        function _co_reset_plugin() {
            function _check_if_settings_changed(defaultOptions, currentOptions) {
                // If lenght is different, we dont have to check values, cuz they are not same objects
                if (Object.keys(defaultOptions).length !== Object.keys(currentOptions).length) return true;
                for (let key of Object.keys(defaultOptions)) { // CHecking values of each key of both objects
                    if (defaultOptions[key] !== currentOptions[key]) {
                        return true;
                    }
                }
                return false
            }
            // Make sure user wants to reset @TODO Check if any settings has been changed, if not no need to reset
            // console.log('getOptions : ', clearout_default_options);
            // console.log('latest Seting : ', clearout_current_options);
            // Check if the defaults and modified are same, if not show prompt,
            if (_check_if_settings_changed(clearout_default_options,clearout_current_options) &&
                confirm('Are you sure you want to revert to the default settings?')){
                document.getElementById("co_reset_options").click();
            };    
        }
        </script>

        <br/>
        <h4 style="font-size: 15px;margin: 0;"><b>Note:</b> </h4>
        <ul style="list-style-type: disc;padding-left: 10px;font-size: 14px;">
            <li>Option <b>Accept only Business Address</b> will supersede other option  during validation </li>
            <li>Validation check will be performed based on the priority orders until one or more condition satisfies</li>
            <li>Priority of option <b>Accept only Business Address</b> is higher than Role or Disposable options. Role and Disposable share the same priority</li>
            <li><a href="mailto:us@clearout.io">Reach out to us</a> if you are looking to have support for additional forms or hooks</li>
            <li>If you are looking for individual <b> form specific custom validation</b>, check out <a href="https://docs.clearout.io/jswidget.html" target="_blank">Clearout JavaScript Widget</a>, the integration can be done without need of developer help. Clearout JS widget provides all bells and whistles to customise the email validation as per your need. </li>
            <li>In case of an incoming email address or domain is already part of <a href="https://app.clearout.io/dashboard/settings/email_verifier" target="_blank">whitelist or blacklist</a> then the verification outcome will be based on that. Above setting options wont have any impact during the verification.</li>
            <li>Know more from <a href='https://wordpress.org/plugins/clearout-email-validator/#how%20to%20opt%20out%20of%20the%20clearout%20email%20validation%20on%20a%20specific%20form%3F' target="_blank">FAQ</a> on how to handle <b>form specific or opting out</b> of email validation</li>
        </ul>
    </div>
    <br/>
    <br/>

    <div style="background-color: #fff;padding:15px 30px 25px 30px;">
        <h3 style="font-size: 1.5em;">Test Plugin Settings</h3>
        <div style="display: flex;align-items: center">
        <input id="clearout_email_address_input" placeholder="Enter a email address" name="clearout_email_address_input" size="30" type="email" value="" style="
   height: 28px;" required/>
        <div id="clearout_validate_button_div" style="margin-left:5px;">
        <input id="clearout_email_address_submit" name="submit" type="submit" value="<?php esc_attr_e('Test'); ?>" class="button button-primary"/>
        </div>
        </div>

        <div id="clearout_result_div"></div>	
    </div>
    </div>

<?php
}

// 
// Settings handles 
function _co_ps_text_setting() {
    return '<h2 style="background: #fff;padding: 15px 10px;">Plugin Settings</h2>';
}

function _co_plugin_api_section_text_setting() {
    echo '<p style="font-size: 15px;">Use below plugin settings to edit the Clearout API Token, Timeout, How & Where the validation need to be performed</p>';
}

function _co_plugin_verification_type_section_text_setting() {
    echo '<p style="font-size: 14px;">By default an email address will be consider as <b>"valid"</b> for status other than <b>"invalid"</b> or <b>"disposable"</b> or <b>"role"</b> or <b>"gibberish"</b>, further it can be fine tuned by choosing one or more options on what to consider as <b>"valid"</b> email address. Clicking <b>"Apply"</b> button will save the settings and changes will come into effect immediately</p>';
}

function _co_plugin_form_section_text_setting() {
    echo '<p style="font-size: 14px;">Choose one or more forms or hooks to perform the email validation</p>';
}

function _co_api_key_label_setting() {
    return '<div><a href="https://app.clearout.io/dashboard/account/api?utm_source=api_token_wp&utm_medium=wp_plugin&utm_campaign=wp_plugins&utm_content=wp_plugin_setting">API Token</a>&nbsp;<i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Generate API Token</span></i></div>';
}

function _co_rolebased_label_setting() {
    return '<div>Role based address as valid&nbsp;<i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Typically associated with a role / group (hr, postmaster, support, sales, etc.) email address instead of a person</span></i></div>';
}

function _co_dispbased_label_setting() {
    return '<div>Disposable address as valid&nbsp;<i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Email address belongs to a temporary account created for a short period of time, like a few hours to a few days</span></i></div>';
}

function _co_gibberishbased_label_setting() {
    return '<div>Gibberish address as valid&nbsp;<i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Allow Email addresses that are gibberish</span></i></div>';
}

function _co_freebased_label_setting() {
    return '<div>Accept only Business address as valid&nbsp;<i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Profressional email addresses that has business domain name after @ instead of Gmail, Yahoo, or Outlook</span></i></div>';
}

function _co_sforms_label_setting() {
    return '<div>Select Forms&nbsp;<i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Select one or more forms that accept email address that require validation</span></i></div>';
}

function _co_shooks_label_setting() {
    return '<div>Select Hooks&nbsp;<i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Select one or more Wordpress hooks to listen and validated incoming email address</span></i></div>';
}

function _co_timeout_label_setting() {
    return '<div>Timeout (in Sec)&nbsp;<i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Time to wait in seconds for the validation to perform</span></i></div>';
}

function _co_custom_invalid_error_label_setting() {
    return '<div>Custom Invalid Error Message&nbsp;<i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Custom error message to display in case of invalid email</span></i></div>';
}

function _co_api_key_setting() {
    $options = get_option('clearout_email_validator');
    $api_key = isset($options['api_key']) ? sanitize_text_field($options['api_key']) : ' ';
    echo '<input id="api_key" name="clearout_email_validator[api_key]" size="60" type="text" value="' . $api_key . '" style="margin-bottom: 5px;" required/><br />';
}

function _co_timeout_setting() {
    $options = get_option('clearout_email_validator');
    $timeout = (isset($options['timeout']) && is_numeric($options['timeout'])) ? $options['timeout'] : '10';
    echo '<input id="timeout" name="clearout_email_validator[timeout]" min="1" max="180" size="10" type="number" value="' . $timeout . '" style="margin-bottom: 5px;" required/><br />';
}

function _co_custom_invalid_error_setting() {
    $options = get_option('clearout_email_validator');
    $custom_invalid_error = isset($options['custom_invalid_error']) ? $options['custom_invalid_error'] : '';
    echo '<input id="custom_invalid_error" placeholder="Type here to override default error message" name="clearout_email_validator[custom_invalid_error]" minlength="1" maxlength="80" size="65" type="text" value="' . $custom_invalid_error . '" style="margin-bottom: 5px;"/><br />';
}

function _co_form_select_setting_option() {
    $options = get_option('clearout_email_validator');
    // Set Global defaults for access in JS as well
    // This is set outside the IF block to prevent warning being thrown for users who are updating extension
    // rather than instaling it fresh
    $defaults = array('role_email_on_off' => '', 'disposable_on_off' => '', 'free_on_off' => '', 'gibberish_on_off' => '',
                        'timeout' => '10', 'api_key' => isset($options['api_key']) ? $options['api_key'] : ' ',
                        'cf7_on_off' => 'on','fmf_on_off' => 'on','cfb_on_off' => 'on','njf_on_off' => 'on',
                        'gvf_on_off' => 'on','rgf_on_off' => 'on','cmf_on_off' => 'on','wpf_on_off' => 'on',
                        'msf_on_off' => 'on','chf_on_off' => 'on','pmp_on_off' => 'on','elm_on_off' => 'on',
                        'flf_on_off' => 'on','wsf_on_off' => 'on','ise_on_off' => 'on');
    // Adding a global var with default and current settings to access them from JS during reset settings logic
    wp_localize_script('my_clearout_plugin_script','clearout_default_options', $defaults);
    wp_localize_script('my_clearout_plugin_script','clearout_current_options', $options);
    if(!isset($options['options_initialized'])) {
        $fmf_on_off = isset($options['fmf_on_off']) ? $options['fmf_on_off'] : 'on';
        $cfb_on_off = isset($options['cfb_on_off']) ? $options['cfb_on_off'] : 'on';
        $cf7_on_off = isset($options['cf7_on_off']) ? $options['cf7_on_off'] : 'on';
        $njf_on_off = isset($options['njf_on_off']) ? $options['njf_on_off'] : 'on';
        $gvf_on_off = isset($options['gvf_on_off']) ? $options['gvf_on_off'] : 'on';
        $rgf_on_off = isset($options['rgf_on_off']) ? $options['rgf_on_off'] : 'on';
        $cmf_on_off = isset($options['cmf_on_off']) ? $options['cmf_on_off'] : 'on';
        $wpf_on_off = isset($options['wpf_on_off']) ? $options['wpf_on_off'] : 'on';
        $msf_on_off = isset($options['msf_on_off']) ? $options['msf_on_off'] : 'on';
        $chf_on_off = isset($options['chf_on_off']) ? $options['chf_on_off'] : 'on';
        $pmp_on_off = isset($options['pmp_on_off']) ? $options['pmp_on_off'] : 'on';
        $elm_on_off = isset($options['elm_on_off']) ? $options['elm_on_off'] : 'on';
        $flf_on_off = isset($options['flf_on_off']) ? $options['flf_on_off'] : 'on';
        $wsf_on_off = isset($options['wsf_on_off']) ? $options['wsf_on_off'] : 'on';
        // update_option('clearout_email_validator', $options);
    } else {
        $fmf_on_off = isset($options['fmf_on_off']) ? $options['fmf_on_off'] : 'off';
        $cfb_on_off = isset($options['cfb_on_off']) ? $options['cfb_on_off'] : 'off';
        $cf7_on_off = isset($options['cf7_on_off']) ? $options['cf7_on_off'] : 'off';
        $njf_on_off = isset($options['njf_on_off']) ? $options['njf_on_off'] : 'off';
        $gvf_on_off = isset($options['gvf_on_off']) ? $options['gvf_on_off'] : 'off';
        $rgf_on_off = isset($options['rgf_on_off']) ? $options['rgf_on_off'] : 'off';
        $cmf_on_off = isset($options['cmf_on_off']) ? $options['cmf_on_off'] : 'off';
        $wpf_on_off = isset($options['wpf_on_off']) ? $options['wpf_on_off'] : 'off';
        $msf_on_off = isset($options['msf_on_off']) ? $options['msf_on_off'] : 'off';
        $chf_on_off = isset($options['chf_on_off']) ? $options['chf_on_off'] : 'off';
        $pmp_on_off = isset($options['pmp_on_off']) ? $options['pmp_on_off'] : 'off';
        $elm_on_off = isset($options['elm_on_off']) ? $options['elm_on_off'] : 'off';
        $flf_on_off = isset($options['flf_on_off']) ? $options['flf_on_off'] : 'off';
        $wsf_on_off = isset($options['wsf_on_off']) ? $options['wsf_on_off'] : 'off';
    }
    
    echo '<div class="sforms-container"><div><input type="checkbox" name="clearout_email_validator[fmf_on_off]" id="fmf_option" value="on"' . checked( $fmf_on_off, 'on', false ) . ' /><label>Formiddable Form </label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[cfb_on_off]" id="cfb_option" value="on"' . checked( $cfb_on_off, 'on', false ) . ' /><label>Contact Form Bws</label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[cf7_on_off]" id="cf7_option" value="on"' . checked( $cf7_on_off, 'on', false ) . ' /><label>Contact Form 7</label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[njf_on_off]" id="njf_option" value="on"' . checked( $njf_on_off, 'on', false ) . ' /><label>Ninja Forms </label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[gvf_on_off]" id="gvf_option" value="on"' . checked( $gvf_on_off, 'on', false ) . ' /><label>Gravity Form </label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[rgf_on_off]" id="rgf_option" value="on"' . checked( $rgf_on_off, 'on', false ) . ' /><label>Registration Form <i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Default Wordpress Registration Form</span></i></label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[cmf_on_off]" id="cmf_option" value="on"' . checked( $cmf_on_off, 'on', false ) . ' /><label>Comment Form <i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Default Wordpress Comment Form</span></i></label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[wpf_on_off]" id="wpf_option" value="on"' . checked( $wpf_on_off, 'on', false ) . ' /><label>WPForms </label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[msf_on_off]" id="msf_option" value="on"' . checked( $msf_on_off, 'on', false ) . ' /><label>Mailster Form </label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[chf_on_off]" id="chf_option" value="on"' . checked( $chf_on_off, 'on', false ) . ' /><label>Checkout Form <i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Default WooCommerce Checkout Form</span></i></label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[pmp_on_off]" id="pmp_option" value="on"' . checked( $pmp_on_off, 'on', false ) . ' /><label>PM Pro Form <i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext">Default Paid Memberships Pro Checkout Form</span></i></label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[elm_on_off]" id="elm_option" value="on"' . checked( $elm_on_off, 'on', false ) . ' /><label>Elementor Form </label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[flf_on_off]" id="flf_option" value="on"' . checked( $flf_on_off, 'on', false ) . ' /><label>Fluent Form </label></div>';
    echo '<div><input type="checkbox" name="clearout_email_validator[wsf_on_off]" id="wsf_option" value="on"' . checked( $wsf_on_off, 'on', false ) . ' /><label>WS Form </label></div>';
    echo '<div><input type="hidden" name="clearout_email_validator[options_initialized]" id="opi_option" value="yes"/></div></div>';
}

function _co_hook_select_setting_option() {
    $options = get_option('clearout_email_validator');
    if(!isset($options['options_initialized'])){
        if (!isset($options['ise_on_off'])) {
            $options['ise_on_off'] = 'on';
        }
    }
    echo '<div class="sforms-container"><div id="ise_container" style="width:100%"><input type="checkbox" name="clearout_email_validator[ise_on_off]" id="ise_option" value="on"' . (isset($options["ise_on_off"]) && $options['ise_on_off'] == 'on' ? 'checked' : 'unchecked') . ' /><label>is_email <i class="fa fa-info-circle apitoken-tooltip"><span class="tooltiptext is_emailtext">Checking this option will replace default Wordpress email validation with Clearout\'s advance email validation </span></i></label><br/><p><b>Warning:</b> Few users noticed that checking this option has an issue triggering email validation multiple times for the same email address, so please monitor the credit usage in <a href="https://app.clearout.io/dashboard/activities" target="_blank">My Activity</a> and if noticed uncheck this option. Also, notice that WooCommerce enabled sites are prone to this issue</p></div></div>';
}

function _co_role_email_setting_option() {
    $options = get_option('clearout_email_validator');
    $role_email_on_off = isset($options['role_email_on_off']) ? $options['role_email_on_off'] : 'off';
    echo '<label><input type="checkbox" name="clearout_email_validator[role_email_on_off]" id="role_email_option" value="on"' . (($role_email_on_off == 'on') ? ' checked' : 'unchecked') . ' /> </label><br/>';
}

function _co_disposable_setting_option() {
    $options = get_option('clearout_email_validator');
    $disposable_on_off = isset($options['disposable_on_off']) ? $options['disposable_on_off'] : 'off';
    echo '<label><input type="checkbox" name="clearout_email_validator[disposable_on_off]" id="disposable_option" value="on"' . (($disposable_on_off == 'on') ? ' checked' : 'unchecked') . ' /></label>';
}

function _co_gibberish_setting_option() {
    $options = get_option('clearout_email_validator');
    $gibberish_on_off = isset($options['gibberish_on_off']) ? $options['gibberish_on_off'] : 'off';
    echo '<label><input type="checkbox" name="clearout_email_validator[gibberish_on_off]" id="gibberish_option" value="on"' . (($gibberish_on_off == 'on') ? ' checked' : 'unchecked') . ' /></label>';
}

function _co_free_setting_option() {
    $options = get_option('clearout_email_validator');
    $free_on_off = isset($options['free_on_off']) ? $options['free_on_off'] : 'off';
    echo '<label><input type="checkbox" name="clearout_email_validator[free_on_off]" id="free_option" value="on"' . (($free_on_off == 'on') ? ' checked' : 'unchecked') . ' /></label>';
}

//
// private methods
// Set default values here
function _co_reset_plugin_options() {
    $options = get_option('clearout_email_validator');
    $defaults = array('role_email_on_off' => '', 'disposable_on_off' => '', 'free_on_off' => '', 'gibberish_on_off' => '',
                        'timeout' => '10', 'api_key' => isset($options['api_key']) ? $options['api_key'] : ' ',
                        'cf7_on_off' => 'on','fmf_on_off' => 'on','cfb_on_off' => 'on','njf_on_off' => 'on',
                        'gvf_on_off' => 'on','rgf_on_off' => 'on','cmf_on_off' => 'on','wpf_on_off' => 'on',
                        'msf_on_off' => 'on','chf_on_off' => 'on','pmp_on_off' => 'on','elm_on_off' => 'on',
                        'flf_on_off' => 'on','wsf_on_off' => 'on','ise_on_off' => 'on'
                    );
    update_option('clearout_email_validator', $defaults);
}