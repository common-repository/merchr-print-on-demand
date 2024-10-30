/* Merchr Orders 1.0.0 */

(function($, hub) {
	'use strict';
	
	// Set default values
	var defaults = hub.getDefaultValues();
	
	/* 
	 * Bind resend buttons
	 * 
	 * Private
	*/
	var bindResendButtons = function() {
		$("#merchr-orders-table").on("click", ".merchr-hub-resend-btn", function(e) {
			e.preventDefault();
			
			// Set button object
			var btn = $(this);
			
			// Set data
			var id = btn.data("id"),
			nonce = btn.data("nonce"),
			url = btn.data("endpoint");
			
			// Show loader
			hub.showHideLoader();
			
			// Make AJAX request
			$.ajax({
				global: false,
				beforeSend: function(request) {
					request.setRequestHeader("App-Request-Type", defaults.header);
				},
				cache: defaults.cacheBool,
				timeout: defaults.expireTime,
				type: defaults.requestType,
				url: url,
				data: 'nonce=' + nonce + '&id=' + id,
				success: (function(data) {
					// Parse JSON Data
					data = JSON && JSON.parse(data) || $.parseJSON(data);
					
					// Hide loader
					hub.showHideLoader();
					
					// Check status
					if(data.status == 'success') {
						hub.showAdminNotice('success', merchrTranslations.orderSentSuccessfully);
						btn.remove();
						$("#merchr-row-" + id + " .merchr-row-status").html(data.payload).css({ 'background' : 'transparent', 'color' : '#2d2d2d' });
					} else {
						hub.showAdminNotice('error', merchrTranslations.orderFailedToSend);
					}
					
					return true;
				}),
				error: (function(request, errorType, errorThrown) { 
					// Hide loader
					hub.showHideLoader();
					
					// Show notice
					hub.showAdminNotice('error', merchrTranslations.errorMsg);
					return false;
				})
			});
			
			return false;
		});
	};
	
	/* 
	 * Wait for document ready
	*/
	$(document).ready(function() {
		if($("#merchr-orders-table").length > 0) {
			bindResendButtons();
		}
	});
})(jQuery, merchrHub);
