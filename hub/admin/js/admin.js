/* Merchr Plug-in 1.0.0 */

var merchrHub = {};
(function(hub, $) {
	'use strict';
	
	/* 
	 * Localised vars
	 * merchrTranslations.errorMsg
	 * merchrTranslations.pleaseWaitMsg
	 * merchrTranslations.fieldsRequired
	 * merchrTranslations.acceptNegativeProfit
	 * merchrTranslations.noProductsSelected
	 * merchrTranslations.importFailed
	 * merchrTranslations.orderSentSuccessfully
	 * merchrTranslations.orderFailedToSend
	*/
	
	/* 
	 * Set vars
	 *
	 * Public
	 */
	hub.width = 0;
	hub.height = 0;
	
	/* 
	 * Set default values
	 * 
	 * Private
	*/
	var defaults = {
		header:          'MERCHR-HUB-AJAX',
		cacheBool:       false,
		expireTime:      12000,
		requestType:     'POST',
		noticeTimeout:   6000,
		formErrorColour: '#cc0000'
 	};
	
	/* 
	 * Debounce function
	 * Public
	 * 
	 * @param object
	 * @param int
	 * @param bool optional
	 * 
	 * @return object
	 */
	hub.debounce = function(func, wait, immediate) {
		var timeout;
		return function() {
			var context = this, args = arguments;
			var later = function() {
				timeout = null;
				if(!immediate) func.apply(context, args);
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if(callNow) func.apply(context, args);
		};
	};
	
	/* 
	 * Show Hide Loader
	 * 
	 * Public
	*/
	hub.showHideLoader = function() {
		// Check if loader active
		var loaderElement = $(".merchr-page-loader");
		if(loaderElement.length > 0) { // Hide it
			loaderElement.hide(200, function() {
				$(this).remove();
			});
		} else { // Create and show it
			var text = '<div class="merchr-loader-text">' + merchrTranslations.pleaseWaitMsg + '</div>',
			close = '<div class="merchr-loader-close">X</div>',
			loader = '<div class="merchr-page-loader">' + close + '<div class="loader"></div>' + text + '</div>';
			$(document.body).append(loader);
			
			// If loader shows for more than set timeout, show close
			setTimeout(function() {
				var loaderClose = $(".merchr-loader-close");
				loaderClose.on("click", function(e) {
					hub.showHideLoader();
				});
				loaderClose.show(200);
			}, defaults.expireTime);
		}
	};
	
	/* 
	 * Get Default Values
	 * 
	 * Public
	*/
	hub.getDefaultValues = function() {
		return defaults;
	};
	
	/* 
	 * Window resize function
	 * Private
	 */
	var resizeEvent = hub.debounce(function() {
		hub.width = window.innerWidth;
		hub.height = window.innerHeight;
	}, 100);
	
	/* 
	 * Fade Out Notices
	 *
	 * @var string
	 * 
	 * Private
	*/
	var fadeOutNotices = function(element) {
		setTimeout(function() {
			$(element).hide(400, function() {
				$(this).remove();
			});
		}, defaults.noticeTimeout);
	};
	
	/* 
	 * Show admin notice
	 *
	 * @var string (error, warning, success, info)
	 * @var string
	 * @var string optional
	 * 
	 * Public
	*/
	hub.showAdminNotice = function(type, content, position) {
		if(typeof position === 'undefined') { 
			position = 'top'; 
		}
		
		// Set notice HTML
		var notice = '<div class="notice notice-' + type + ' merchr-hub-notice">' + content + '</div>';
		
		// Show notice depending on position
		if(position == 'top') {
			$(".merchr-title").after(notice);
		} else {
			$(".merchr-hub-wrapper").after(notice);
		}
		
		// Fade out notice after x timeout
		fadeOutNotices(".merchr-hub-notice");
	};
	
	/* 
	 * Show form notice
	 *
	 * @var string (error, warning, success)
	 * @var string
	 * @var string optional
	 * 
	 * Private
	*/
	var showFormNotice = function(type, content, element) {
		if(typeof element === 'undefined' || element === '') { 
			element = '.merchr-hub-form-notice-wrapper'; 
		}
		
		// Set notice HTML
		var notice = '<div class="merchr-hub-form-notice merchr-hub-form-notice-' + type + '">' + content + '</div>';
		
		// Add to form Area
		$(element).html(notice);
		
		// Fade out notice after x timeout
		fadeOutNotices(element + " div");
	};
	
	/* 
	 * Update page Content
	 * 
	 * @var string
	 * @var string
	 * 
	 * Public
	*/
	hub.updatePageContent = function(element, html, clear) {
		// Fade out content, replace and fade in
		$(element).hide(200, function() {
			$(this).html(html);
			$(this).show(200, function() {
				$("html, body").animate({
					scrollTop: $(".merchr-hub-wrapper").offset().top - 32
				}, 800);
			});
		});
		
		// Check if we are to clear content
		if(typeof clear !== 'undefined') {
			$(clear).hide(200, function() {
				$(this).html('');
				
				// Check if setup page and reset background
				var startWrapperElement = $(".merchr-hub-start-here-wrapper");
				if(startWrapperElement.length == 1) {
					// Add no image class
					startWrapperElement.addClass("merchr-hub-no-background");
				}
			});
		}
	};
	
	/* 
	 * Basic Form Validation
	 * 
	 * @var string
	 * 
	 * Private
	*/
	var basicFormValidation = function(element) {
		var result = true;
		// Check all fields for value with merchr-required class
		element.find(".merchr-required").each(function(i) {
			var value = $(this).val();
			if(value == '') {
				result = false;
				
				var originalBorder = $(this).css("border-color");
				$(this).css("border-color", defaults.formErrorColour);
				
				// Restore colour on blur
				$(this).on("blur", function() {
					$(this).css("border-color", originalBorder);
				});
			}
		});
		
		return result;	
	};
		
	/* 
	 * Disable Enable Submit
	 * 
	 * @var object (jQuery)
	 * @var string
	 *
	 * Private
	*/
	var disableEnableSubmit = function(form, action) {
		var element = form.find(".merchr-hub-submit");
		if(action == 'disable') {
			element.prop('disabled', true);
		} else {
			element.prop('disabled', false);
		}
	};
	
	/* 
	 * Bind page buttons
	 * 
	 * Private
	*/
	var bindPageButtons = function() {
		$(".merchr-hub-wrapper").on("click", ".merchr-hub-page-btn", function(e) {
			e.preventDefault();
			
			var href = $(this).attr("href"),
			nonce = $(this).data("nonce"),
			target = $(this).data("target"),
			clear = $(this).data("clear");
			
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
				url: href,
				data: 'nonce=' + nonce,
				success: (function(data) {
					// Parse JSON Data
					data = JSON && JSON.parse(data) || $.parseJSON(data);
					
					// Hide loader
					hub.showHideLoader();
					
					// Check status
					if(data.status == 'success') {
						// Update page content
						hub.updatePageContent(target, data.payload, clear);
					} else {
						// Show error notice
						hub.showAdminNotice('error', data.msg, 'bottom');
					}
					
					return true;
				}),
				error: (function(request, errorType, errorThrown) { 
					// Hide loader
					hub.showHideLoader();
					
					// Show notice
					hub.showAdminNotice('error', merchrTranslations.errorMsg, 'bottom');
					return false;
				})
			});
			
			return false;
		});
	};
	
	/* 
	 * Bind Forms
	 * 
	 * Private
	*/
	var bindForms = function() {
		$(".merchr-wrap").on("submit", ".merchr-hub-form", function(e) {
			e.preventDefault();
			
			var form = $(this),
			action = form.attr("action"),
			handle = form.data("handle"),
			target = form.data("target"),
			clear = form.data("clear"),
			noticeElement = form.data("notice-element");
			
			// First do basic validation
			if(!basicFormValidation(form)) {
				showFormNotice('error', merchrTranslations.fieldsRequired, noticeElement);
				return false;
			}
			
			// Disable submit
			disableEnableSubmit(form, 'disable');
			
			// Serialise from
			var values = form.serialize();
			
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
				url: action,
				data: values,
				success: (function(data) {
					// Parse JSON Data
					data = JSON && JSON.parse(data) || $.parseJSON(data);
					
					// Hide loader and enable submit
					hub.showHideLoader();
					disableEnableSubmit(form, 'enable');
					
					// Check status
					if(data.status == 'success') {
						// Check for page or form
						if(handle == 'page') {
							// Update page content
							hub.updatePageContent(target, data.payload, clear);
						} else {
							// Show form notice
							showFormNotice('success', data.msg, noticeElement);
						}
					} else {
						// Show error notice
						showFormNotice('error', data.msg, noticeElement);
					}
					
					return true;
				}),
				error: (function(request, errorType, errorThrown) { 
					// Hide loader and enable submit
					hub.showHideLoader();
					disableEnableSubmit(form, 'enable');
					
					// Show notice
					showFormNotice('error', merchrTranslations.errorMsg, noticeElement);
					return false;
				})
			});
			
			return false;
		});
	};
	
	/* 
	 * Bind Form Toggle
	 *
	 * Private
	*/
	var bindFormToggle = function() {
		// Bind API link to show account form
		$(".merchr-wrap").on("click", "#merchr-no-api-key strong", function(e) {
			// Hide API form
			$("#merchr-hub-api-form").hide(200, function() {
				$("#merchr-hub-account-form").show(200);
			});
		});
			
		// Bind account link to show API form
		$(".merchr-wrap").on("click", "#merchr-has-api-key strong", function(e) {
			// Hide account form
			$("#merchr-hub-account-form").hide(200, function() {
				$("#merchr-hub-api-form").show(200);
			});
		});
	};
	
	/* 
	 * Bind Tooltips
	 *
	 * Private
	*/
	var bindTooltips = function() {
		if(hub.width > 768) {
			setTimeout(function() {
				$(".merchr-tooltip").tooltip({
					position: {
						my: "left top",
						at: "right+5 top+2",
						collision: "none"
					}
				});
			}, 250);
		}
	};
	
	/* 
	 * Wait for document ready
	*/
	$(document).ready(function() {
		if($(".merchr-wrap").length > 0) {
			// Set initial width and height and bind resize event with debounce
			hub.width = window.innerWidth;
			hub.height = window.innerHeight;
			window.addEventListener('resize', resizeEvent);
			
			// Call other methods
			bindPageButtons();
			bindForms();
			bindFormToggle();
			bindTooltips();
		}
	});
})(merchrHub, jQuery);
