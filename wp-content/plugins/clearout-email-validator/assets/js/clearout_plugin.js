jQuery(document).ready(function () {
	jQuery('body').on('click', '#clearout_email_address_submit', function (e) {
		e.preventDefault();

		if (e.handled !== true) { // This will prevent event triggering more then once
			event.handled = true;
		} else {
			// to handle multiple callback
			return
		}

		if (!jQuery('#clearout_email_address_input').val()) {
			return
		}

		if (jQuery('#clearout_email_address_input').val().length > 320) {
			jQuery("#clearout_result_div").html("<p style='font-size:14px;display: flex;align-items: center;'><i class='fa fa-times-circle' style='font-size:20px;color:red;'></i>&nbsp;&nbsp;Invalid - Email address length exceed 320 characters </p>");
			return
		}
		jQuery("#clearout_validate_button_div").html("<button id='clearout_email_address_submit' class='button button-primary'><i class='fa fa-spinner fa-spin'></i>&nbsp;&nbsp;Validating...</button>");
		// console.log('See the sanitize data',sanitize_email(jQuery('#clearout_email_address_input').val()))
		jQuery.ajax({
			url: clearout_plugin_ajax_call.ajax_url,
			type: 'post',
			data: {
				action: 'co_test_plugin_setting_action',
				clearout_email: jQuery('#clearout_email_address_input').val(),
				clearout_timeout: jQuery('#timeout').val() * 1000
			},
			success: function (response /** raw response object */) {
				jQuery("#clearout_validate_button_div").html("<input id='clearout_email_address_submit' name='Submit' type='submit' value='Test' class='button button-primary'/>");
				if (response && response.status === 'success') {
					// response = JSON.parse(response.body);
					// jQuery('.clearout_email_address_input').html(response);
					if (response.data.status === false) { //invald email
						jQuery("#clearout_result_div").html("<p style='font-size:14px;display: flex;align-items: center;'><i class='fa fa-times-circle' style='font-size:20px;color:red;'></i>&nbsp;&nbsp;Invalid - " + response.data.reason + "</p>");
					} else {
						jQuery("#clearout_result_div").html("<p style='font-size:14px;display: flex;align-items: center;'><i class='fa fa-check-circle' style='font-size:20px;color:green;'></i>&nbsp;&nbsp;" + response.data.reason + "</p>");
					}
				} else {
					jQuery("#clearout_result_div").html("<p style='font-size:14px;display: flex;align-items: center;'><i class='fa fa-times-circle' style='font-size:20px;color:red;'></i>&nbsp;&nbsp;Something went wrong please contact us@clearout.io</p>");
				}
			},
			error: function (request, status, error) {
				let errors = JSON.parse(request.responseText);
				//jQuery('#result_email_valid').text(errors.error.message);
				jQuery("#clearout_result_div").html("<p style='font-size:14px;display: flex;align-items: center;'><i class='fa fa-times-circle' style='font-size:20px;color:red;'></i>&nbsp;&nbsp;" + errors.error.message + "</p>");

			}
		});
	});
});
