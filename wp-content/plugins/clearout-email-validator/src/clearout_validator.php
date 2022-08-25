<?php

// Public methods called by ajax.php 
function co_test_plugin_setting_action()
{
    $clearout_options = get_option('clearout_email_validator');
    // Check if API token is set eg. When plugin is installed for first time and api token isnt set.
    if (!isset($clearout_options['api_key']) || empty($clearout_options['api_key'])) {
        $response['status'] = 'success';
        $response['data'] = array();
        $response['data']['reason'] = esc_html__('API token not set, please set the token above and apply the settings');
        $response['data']['status'] = false;
        wp_send_json($response);
        exit();
    }

    $sanitized_email = sanitize_email($_POST['clearout_email']);
    $response = array();
    // Check if sanitised email has returned empty i.e syntax errors
    if (empty($sanitized_email)) {
        $response['status'] = 'success';
        $response['data'] = array();
        // $response['data']['reason'] = esc_html__('You have entered an invalid email address, Please try again with a valid email address');
        $response['data']['reason'] = (_check_custom_error_msg_exist($clearout_options)) ? esc_html__($clearout_options['custom_invalid_error']) : esc_html__('You have entered an invalid email address, Please try again with a valid email address');
        $response['data']['status'] = false;
        wp_send_json($response);
        exit();
    }
    $validation_result = _co_email_validation($sanitized_email, $clearout_options, CLEAROUT_TEST_PLUGIN_SOURCE);
    $message = 'You have entered valid email address';
    if ($validation_result['reason'] != 'valid_email') {
        $message = _get_error_message($validation_result['reason']);
    }
    $message = (isset($clearout_options['custom_invalid_error']) && $validation_result['reason'] != 'valid_email' && trim($clearout_options['custom_invalid_error']) != '') ? $clearout_options['custom_invalid_error'] : $message;
    $validation_result['reason'] = $message;
    // compose response object
    $response['status'] = 'success';
    $response['data'] = $validation_result;
    wp_send_json($response);
    exit();
}

// Method to check if there is any custom error msg set and if it isnt empty after trimmed
function _check_custom_error_msg_exist($clearout_options)
{
    $ret = false;
    if (isset($clearout_options['custom_invalid_error']) && !empty(trim($clearout_options['custom_invalid_error']))) {
        $ret = true;
    }
    return $ret;
}

function _get_current_page_url() {
    $page_url = '';
    try { //try to get full url if possible?
        $page_url = $_SERVER['HTTP_REFERER'];
    } catch (Exception $e) {
        $page_url = get_site_url();
    }
    return $page_url;
}

function _co_verify_email($emailAddress, $api_key, $timeout, $clearout_form_source, $use_cache)
{
    $emailAddress = strtolower($emailAddress);

    if ($use_cache === true) {
        $cacheKey = 'clearout_' . $emailAddress;
        $cacheValue = get_transient($cacheKey);
        if ($cacheValue) {
            return $cacheValue;
        }
    }

    $data = NULL;
    try {
        global $wp;
        // $page_url = add_query_arg( $wp->query_vars, home_url() );
        $page_url = _get_current_page_url();

        // Now we need to send the data to CLEAROUT API Token and return back the result.
        $url = CLEAROUT_EMAIL_VERIFY_API_URL . '&r=fs&source=wordpress&fn=' . $clearout_form_source . '&pu=' . urlencode($page_url);
        $args = array(
            'method' => 'POST',
            'data_format' => 'body',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer:' . str_replace(' ', '', $api_key)
            ),
            'body' => json_encode(array('email' => $emailAddress, 'timeout' => $timeout * 1000)),
            'timeout' => $timeout + 2 // http request timeout with 2 secs grace interval  
        );

        // Now we used WordPress custom HTTP API method to get the result from CLEAROUT API.
        $results = wp_remote_post($url, $args);
        $response_code = wp_remote_retrieve_response_code($results);
        if (!is_wp_error($results)) {
            $body = wp_remote_retrieve_body($results);
            // Decode the return json results and return the data.
            $data = json_decode($body, true);
        }

        //set transient for response verified email only if resp status === 200
        if ($use_cache === true && $response_code === CLEAROUT_HTTP_OK_STATUS_CODE && !empty($data['data'])) { 
            set_transient($cacheKey, $data, CLEAROUT_RESULT_CACHED_TIMEOUT);
        }
    } catch (Exception $e) {
        $data = NULL;
    }

    return $data;
}


// Private methods
function _co_is_role_email($emailResult)
{
    $is_role = false;
    if ($emailResult['data']['role'] == 'yes') {
        $is_role = true;
    }
    return $is_role;
}

function _co_is_free($emailResult)
{
    $is_free = false;
    if ($emailResult['data']['free'] == 'yes') {
        $is_free = true;
    }
    return $is_free;
}

function _co_is_disposable($emailResult)
{
    $is_disposable = false;
    if ($emailResult['data']['disposable'] == 'yes') {
        $is_disposable = true;
    }
    return $is_disposable;
}

function _co_is_gibberish($emailResult)
{
    $is_gibberish = false;
    if ($emailResult['data']['gibberish'] == 'yes') {
        $is_gibberish = true;
    }
    return $is_gibberish;
}

function _co_email_validation($email, $clearout_options, $clearout_form_source)
{
    $clearout_validation_result = array();
    $use_cache = true;

    // dont use cache for test plugin call
    if ($clearout_form_source === CLEAROUT_TEST_PLUGIN_SOURCE) {
        $use_cache = false;
    }

    $emailResult = _co_verify_email($email, $clearout_options['api_key'], $clearout_options['timeout'], $clearout_form_source, $use_cache);

    // Check if valid API Token is present (Only for the 'co-test-plugin' form)
    if (
        $clearout_form_source == CLEAROUT_TEST_PLUGIN_SOURCE &&
        $emailResult['status'] == 'failed' && $emailResult['error']['code'] == 1000
    ) {
        $clearout_validation_result['status'] = false;
        $clearout_validation_result['reason'] = 'api_token';
        return $clearout_validation_result;
    }

    // something went wrong with validation, so always return valid email
    $clearout_validation_result['status'] = true;
    $clearout_validation_result['reason'] = 'valid_email';

    if (empty($emailResult) || empty($emailResult['data'])) {
        // $clearout_validation_result['reason'] = 'unknown_email'; //rename to unknown
        return $clearout_validation_result;
    }

    // Check for whitelisting of the emai/domain
    if ($emailResult['data']['sub_status']['code'] == CLEAROUT_VERIFICATION_EMAIL_WHITELISTED_SUBSTATUS_CODE || $emailResult['data']['sub_status']['code'] == CLEAROUT_VERIFICATION_DOMAIN_WHITELISTED_SUBSTATUS_CODE) {
        return $clearout_validation_result;
    }

    // check the verification status of email address is invalid
    if ($emailResult['data']['status'] == 'invalid') {
        $clearout_validation_result['status'] = false;
        $clearout_validation_result['reason'] = 'invalid_email';
        return $clearout_validation_result;
    }

    // check option filters
    // does user checked only to accept business (non-free) email?
    if (isset($clearout_options['free_on_off']) && $clearout_options['free_on_off'] == 'on') {
        $is_free = _co_is_free($emailResult);
        if ($is_free) {
            $clearout_validation_result['status'] = false;
            $clearout_validation_result['reason'] = 'free_email';
            return $clearout_validation_result;
        }
    }
    // does user checked role based email address as  valid?
    if (!(isset($clearout_options['role_email_on_off']) && $clearout_options['role_email_on_off'] == 'on')) {
        $is_role = _co_is_role_email($emailResult);
        if ($is_role) {
            $clearout_validation_result['status'] = false;
            $clearout_validation_result['reason'] = 'role_email';
            return $clearout_validation_result;
        }
    }

    // does user checked disposable email address as valid? 
    if (!(isset($clearout_options['disposable_on_off']) && $clearout_options['disposable_on_off'] == 'on')) {
        $is_disposable = _co_is_disposable($emailResult);
        if ($is_disposable) {
            $clearout_validation_result['status'] = false;
            $clearout_validation_result['reason'] = 'disposable_email';
            return $clearout_validation_result;
        }
    }

    // does user checked gibberish email address as valid? 
    if (!(isset($clearout_options['gibberish_on_off']) && $clearout_options['gibberish_on_off'] == 'on')) {
        $is_gibberish = _co_is_gibberish($emailResult);
        if ($is_gibberish) {
            $clearout_validation_result['status'] = false;
            $clearout_validation_result['reason'] = 'gibberish_email';
            return $clearout_validation_result;
        }
    }
    // Control comes here if no filters are selected i.e Only valid/INvalid email
    return $clearout_validation_result;
}

// Method to get the error Message Based on the Error Status
function _get_error_message($errorStatus)
{
    $errorMessage = 'This email address is invalid or not allowed - please check';
    switch ($errorStatus) {
        case 'api_token':
            $message = 'Invalid API Token, please check your API token';
            break;
        case 'invalid_email':
            $errorMessage = 'You have entered an invalid email address, Please try again with a valid email address';
            break;
        case 'disposable_email':
            $errorMessage = 'You have entered disposable email address, Please try again with non disposable email address';
            break;
        case 'free_email':
            $errorMessage = 'You have entered free service email address, Please try again with business / work email address';
            break;
        case 'role_email':
            $errorMessage = 'You have entered role-based email address, Please try again with non role-based email address';
            break;
        case 'gibberish_email':
            $errorMessage = 'You have entered a gibberish email address, please try again with a proper email';
            break;
        default:
            $errorMessage = 'This email address is not allowed due to ' . $errorStatus;
    }
    return $errorMessage;
}

function clearout_email_validator_filter($email)
{
    $is_valid_email = true;

    if (empty($email)) {
        return false;
    }

    if (($_SERVER['REQUEST_URI'] == '/wp-login.php') | ($_SERVER['REQUEST_URI'] == '/wp-login.php?loggedout=true')
        | ($_SERVER['REQUEST_URI'] == '/wp-cron.php')
    ) {
        // if wp-login.php is been called for login to dashboard, skip the check.
        return $is_valid_email;
    }

    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $cruser_email = $current_user->user_email;
        if ($email == $cruser_email) {
            return $is_valid_email;
        }
    }

    // Get option settings to know which validator is been called
    $clearout_options = get_option('clearout_email_validator');
    $clearout_form_source = "custom";
    if ((!($clearout_options['api_key'] == ''))) {
        // do the email validation
        $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
        if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
            if ($validation_result['status'] == false) {
                $is_valid_email = false;
            }
        }
    }
    return $is_valid_email;
}


function clearout_mailster_email_validator($result)
{
    $clearout_form_source = "mailsterform";

    if (isset($result['email'])) {
        $email = $result['email'];

        // Get option settings to know which validator is been called
        $clearout_options = get_option('clearout_email_validator');
        $is_valid_email = true;
        if (($clearout_options['api_key'] == '') || ($email == '')) {
            return $result;
        }
        // do the email validation
        $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
        if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
            if ($validation_result['status'] == false) {
                $is_valid_email = false;

                // To Support custom error mesage before going to switch case
                if (_check_custom_error_msg_exist($clearout_options)) {
                    return new WP_Error('email', $clearout_options['custom_invalid_error']);
                }

                $errorMessage = _get_error_message($validation_result['reason']);
                return new WP_Error('email', $errorMessage);
            }
        }
    }

    return $result;
}

function clearout_email_validator_wprg($errors, $sanitized_user_login, $email)
{
    $clearout_form_source = "wprg";

    if (email_exists($email)) {
        return $errors;
    }

    // Get option settings to know which validator is been called
    $clearout_options = get_option('clearout_email_validator');
    $is_valid_email = true;
    if (($clearout_options['api_key'] == '') || ($email == '')) {
        return $errors;
    }
    // do the email validation
    $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
    if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
        if ($validation_result['status'] == false) {
            $is_valid_email = false;
            $errors->add('invalid_email', ((_check_custom_error_msg_exist($clearout_options)) ? esc_html__($clearout_options['custom_invalid_error']) : esc_html__('This email address is invalid or not allowed - please check.', 'clearout-email-validator')));
            return $errors;
        }
    }
    return $errors;
}

function clearout_pmpro_signup_email_validate($pmpro_continue_registration)
{
    $email = $_POST['bemail'];

    if (empty($email)) {
        return false;
    }

    // Get option settings to know which validator is been called
    $clearout_options = get_option('clearout_email_validator');
    $clearout_form_source = "pmpro";
    if ((!($clearout_options['api_key'] == ''))) {
        // do the email validation
        $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
        if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
            if ($validation_result['status'] == false) {
                $pmpro_continue_registration = false;
                if (_check_custom_error_msg_exist($clearout_options)) {
                    pmpro_setMessage(esc_html__($clearout_options['custom_invalid_error']));
                } else {
                    $errorMessage = _get_error_message($validation_result['reason']);
                    pmpro_setMessage(esc_html__($errorMessage));
                }
            }
        }
    }
    return $pmpro_continue_registration;
}

function clearout_gvf_email_validator($result, $value, $form, $field)
{
    $clearout_form_source = "gvf";

    if ($field->type == 'email' && $field->isRequired == '0' && $value == '') {
        $result['is_valid'] = true;
        return $result;
    }
    if ($field->type == 'email' && $result['is_valid']) {
        // Get option settings to know which validator is been called
        $clearout_options = get_option('clearout_email_validator');
        $is_valid_email = true;
        if (($clearout_options['api_key'] == '') && ($value != '')) {
            return $result;
        }
        // do the email validation
        $validation_result = _co_email_validation($value, $clearout_options, $clearout_form_source);
        if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
            if ($validation_result['status'] == false) {
                $is_valid_email = false;
                $result['is_valid'] = false;
                if (_check_custom_error_msg_exist($clearout_options)) {
                    $result['message'] = esc_html__($clearout_options['custom_invalid_error']);
                } else {
                    $errorMessage = _get_error_message($validation_result['reason']);
                    $result['message'] = esc_html__($errorMessage);
                }
                return $result;
            }
        }
    }

    return $result;
}

function clearout_wpf_email_validator($entry, $form_data)
{
    $clearout_form_source = "wpf";
    $hidden_ignore_fields = preg_grep(CLEAROUT_IGNORE_VALIDATION_IDENTIFIER_REGEX, $entry['fields']);
    if (count($hidden_ignore_fields) > 0) {
        return $form_data;
    }
    foreach ($entry['fields'] as $key => $field) {
        $value = $field;
        // ignore multi-line strings / textareas
        if (is_string($value) && preg_match('/@.+\./', $value) && strpos($value, "\n") === false) {
            $email = sanitize_email($value);
            if (empty($email)) {
                $field_id = $field['id'];
                wpforms()->process->errors[$form_data['id']]['header'] = esc_html__(
                    'This email address is invalid or not allowed - please check.',
                    'clearout-email-validator'
                );
                return $form_data;
            }

            // Get option settings to know which validator is been called
            $clearout_options = get_option('clearout_email_validator');
            if ((!($clearout_options['api_key'] == '')) && ($email != '')) {
                // do the email validation
                $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
                if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
                    if ($validation_result['status'] == false) {
                        if (_check_custom_error_msg_exist($clearout_options)) {
                            wpforms()->process->errors[$form_data['id']]['header']  = esc_html__($clearout_options['custom_invalid_error']);
                            return;
                        }
                        $errorMessage = _get_error_message($validation_result['reason']);
                        wpforms()->process->errors[$form_data['id']]['header']  = esc_html__($errorMessage);
                    }
                }
            }
        }
    }
}

function clearout_ninja_email_validator($form_data)
{
    $clearout_form_source = "ninja";
    $hidden_ignore_fields = preg_grep(CLEAROUT_IGNORE_VALIDATION_IDENTIFIER_REGEX, array_column($form_data['fields'], 'key'));
    if (count($hidden_ignore_fields) > 0) return $form_data;

    foreach ($form_data['fields'] as $key => $field) {
        $value = $field['value'];
        // ignore multi-line strings / textareas
        if (is_string($value) && preg_match('/@.+\./', $value) && strpos($value, "\n") === false) {
            $email = sanitize_email($value);
            if (empty($email)) {
                $field_id = $field['id'];
                $form_data['errors']['fields'][$field_id] = esc_html__(
                    'This email address is invalid or not allowed - please check.',
                    'clearout-email-validator'
                );
                return $form_data;
            }

            // Get option settings to know which validator is been called
            $clearout_options = get_option('clearout_email_validator');
            if ((!($clearout_options['api_key'] == '')) && ($value != '')) {
                // do the email validation
                $validation_result = _co_email_validation($value, $clearout_options, $clearout_form_source);
                if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
                    if ($validation_result['status'] == false) {
                        $field_id = $field['id'];
                        if (_check_custom_error_msg_exist($clearout_options)) {
                            $form_data['errors']['fields'][$field_id] = esc_html__($clearout_options['custom_invalid_error']);
                        } else {
                            $errorMessage = _get_error_message($validation_result['reason']);
                            $form_data['errors']['fields'][$field_id] = esc_html__($errorMessage, 'clearout-email-validator');
                        }

                        return $form_data;
                    } else {
                        return $form_data;
                    }
                } else {
                    return $form_data;
                }
            } else {
                // If the user do not enter the API Token, or ignore the admin notice, or the $email is empty, just let it pass.
                return $form_data;
            }
        }
    }
}

function clearout_wpcf7_custom_email_validator_filter($result, $tags)
{
    $clearout_form_source = "contactform7";
    // Get option settings to know which validator is been called
    $clearout_options = get_option('clearout_email_validator');
    $tags = new WPCF7_FormTag($tags);
    $type = $tags->type;
    $name = $tags->name;
    $email = sanitize_email($_POST[$name]);

    if (($clearout_options['api_key'] == '') || ($email == '')) {
        return $result;
    }
    if (empty($email) && 'email*' == $type) {
        $result->invalidate(
            $tags,
            esc_html__(
                'You have entered an invalid email address, Please try again with a valid email address',
                'clearout-email-validator'
            )
        );
        return $result;
    }
    $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
    if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
        if ($validation_result['status'] == false) {
            if (_check_custom_error_msg_exist($clearout_options)) {
                $result->invalidate($tags, esc_html__($clearout_options['custom_invalid_error']));
                return $result;
            }
            $errorMessage = _get_error_message($validation_result['reason']);
            $result->invalidate($tags, esc_html__($errorMessage, 'clearout-email-validator'));
        }
    }
    return $result;
}

function clearout_frm_validate_entry($errors, $values)
{
    foreach ($values['item_meta'] as $key => $value) {
        if (gettype($value) != 'string') continue;
        if (preg_match(CLEAROUT_IGNORE_VALIDATION_IDENTIFIER_REGEX, $value)){
            return $errors;
        }
    }
    if (count($hidden_ignore_fields) > 0) {
        return $errors;
    }
    $clearout_form_source = "formidable";
    foreach ($values['item_meta'] as $key => $value) {
        if (is_string($value) &&  preg_match("/^\S+@\S+\.\S+$/", $value)) {
            $clearout_options = get_option('clearout_email_validator');
            $email = sanitize_email($value);

            if (empty($email)) {
                $errors['ct_error'] = esc_html__(
                    'You have entered an invalid email address, Please try again with a valid email address',
                    'clearout-email-validator'
                );
                return $errors;
            }

            if (($clearout_options['api_key'] != '') && ($email != '')) {
                $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
                if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
                    if ($validation_result['status'] == false) {
                        if (_check_custom_error_msg_exist($clearout_options)) {
                            $errors['ct_error'] = esc_html__($clearout_options['custom_invalid_error']);
                            return $errors;
                        }
                        $errorMessage = _get_error_message($validation_result['reason']);
                        $errors['ct_error'] = esc_html__($errorMessage, 'clearout-email-validator');
                    }
                }
            }
        }
    }
    return $errors;
}

function clearout_bws_validate_email()
{
    global $cntctfrm_error_message;
    $clearout_form_source = "bestwebsoft";
    if (!(empty($_POST['cntctfrm_contact_email'])) && ($_POST['cntctfrm_contact_email'] != '')) {
        $clearout_options = get_option('clearout_email_validator');
        $email = sanitize_email($_POST['cntctfrm_contact_email']);

        if (empty($email)) {
            $cntctfrm_error_message['error_email'] = esc_html__(
                'You have entered an invalid email address, Please try again with a valid email address',
                'clearout-email-validator'
            );
            return;
        }

        if (($clearout_options['api_key'] != '') && ($email != '')) {
            $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
            if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
                if ($validation_result['status'] == false) {
                    if (_check_custom_error_msg_exist($clearout_options)) {
                        $cntctfrm_error_message['error_email'] = esc_html__($clearout_options['custom_invalid_error']);
                        return;
                    }
                    $errorMessage = _get_error_message($validation_result['reason']);
                    $cntctfrm_error_message['error_email'] = esc_html__($errorMessage, 'clearout-email-validator');
                }
            }
        }
    }
}

function clearout_woocom_checkout_validate_email($fields, $errors)
{
    $clearout_form_source = "woochkfrm";

    if (isset($fields['billing_email'])) {
        $email = $fields['billing_email'];

        // Get option settings to know which validator is been called
        $clearout_options = get_option('clearout_email_validator');
        $is_valid_email = true;
        if ((!($clearout_options['api_key'] == '')) && ($email != '')) {
            // do the email validation
            $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
            if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
                if ($validation_result['status'] == false) {
                    $is_valid_email = false;
                    // if any validation errors
                    if (!empty($errors->get_error_codes())) {

                        // remove all of them
                        foreach ($errors->get_error_codes() as $code) {
                            $errors->remove($code);
                        }

                        if (_check_custom_error_msg_exist($clearout_options)) {
                            $errors->add('validation', $clearout_options['custom_invalid_error']);
                            return;
                        }
                        $errorMessage = _get_error_message($validation_result['reason']);
                        $errors->add('validation', $errorMessage);
                    }
                }
            }
        }
    }
}

function clearout_elementor_email_validator($field, $record, $ajax_handler)
{
    $clearout_form_source = "elementor";

    if (preg_match(CLEAROUT_IGNORE_VALIDATION_IDENTIFIER_REGEX, $field['id'])) {
        return;
    }
    if (empty($field['value'])) {
        $ajax_handler->add_error($field['id'], 'You have entered an invalid email address, Please try again with a valid email address');
        return;
    }

    // Get option settings to know which validator is been called
    $clearout_options = get_option('clearout_email_validator');
    if (!($clearout_options['api_key'] == '')) {
        // Email Validation
        $validation_result = _co_email_validation($field['value'], $clearout_options, $clearout_form_source);
        if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
            if ($validation_result['status'] == false) {
                // $ajax_handler->add_error ( $field['id'], "This email address is invalid or not allowed - please check.");
                if (_check_custom_error_msg_exist($clearout_options)) {
                    $ajax_handler->add_error($field['id'], $clearout_options['custom_invalid_error']);
                    return;
                }
                $errorMessage = _get_error_message($validation_result['reason']);
                $ajax_handler->add_error($field['id'], $errorMessage);
            }
        }
    }
}

function clearout_fluent_email_validator($errorMessage, $field, $formData, $fields, $form)
{
    $clearout_form_source = "fluent";

    if (isset($formData['email'])) {
        $email = $formData['email'];
        // Get option settings to know which validator is been called
        $clearout_options = get_option('clearout_email_validator');
        $is_valid_email = true;
        if ((!($clearout_options['api_key'] == '')) && ($email != '')) {
            // do the email validation
            $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
            if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
                if ($validation_result['status'] == false) {
                    $is_valid_email = false;
                    if (_check_custom_error_msg_exist($clearout_options)) {
                        $errorMessage = [$clearout_options['custom_invalid_error']];
                    } else {
                        $errorMessage = [_get_error_message($validation_result['reason'])];
                    }
                }
            }
        }
    }
    return $errorMessage;
}

function clearout_wsf_email_validator($valid, $email)
{
    $clearout_form_source = "wsf";

    // Empty email check, in case user doesnt prevent empty submission in form
    if (empty($email)) {
        return __('Email field is empty');
    }
    // Get option settings to know which validator is been called
    $clearout_options = get_option('clearout_email_validator');
    $is_valid_email = true;
    if ((!($clearout_options['api_key'] == '')) && ($email != '')) {
        // do the email validation
        $validation_result = _co_email_validation($email, $clearout_options, $clearout_form_source);
        if ((is_array($validation_result)) && array_key_exists('status', $validation_result)) {
            if ($validation_result['status'] == false) {
                $is_valid_email = false;
                if (_check_custom_error_msg_exist($clearout_options)) {
                    $errorMessage = $clearout_options['custom_invalid_error'];
                } else {
                    $errorMessage = _get_error_message($validation_result['reason']);
                }
            }
        }
    }
    return $is_valid_email ? $is_valid_email : __($errorMessage);
}
