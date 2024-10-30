/* Merchr Products 1.0.0 */

(function($, hub) {
	'use strict';
	
	// Set default values
	var defaults = hub.getDefaultValues();
	
	/* 
	 * Process Required Field
	 *
	 * @param object
	 * 
	 * Private
	*/
	var processRequiredField = function(ele) {
		var originalBorderColour = ele.css("border-color");
		ele.css({'border-color' : '#cc0000'});
		ele.on("blur", function(e) {
			ele.css({'border-color' : originalBorderColour});
		});
	};
	
	/* 
	 * Show Error Message
	 *
	 * @param string
	 * 
	 * Private
	*/
	var showErrorMessage = function(msg) {
		var ele = $(".featherlight-inner .merchr-edit-product-error");
		
		ele.html("<span>" + msg + "</span>");
		
		setTimeout(function() {
			ele.find("span").fadeOut(200, function() {
				ele.html("");
			});
		}, 6000);
	};
	
	/* 
	 * Bind Edit Form
	 *
	 * @param int
	 * 
	 * Private
	*/
	var bindEditForm = function(id) {
		var negativeProfit = false;
		
		$(".featherlight-inner").on("click", ".merchr-hub-btn", function(e) {
			e.preventDefault();
			
			// Check required fields have a value
			var name = $(".featherlight-inner .product-title-field"),
			description = $(".featherlight-inner .product-description-field"),
			price = $(".featherlight-inner .product-price-field");
			
			if(name.val() == '') {
				processRequiredField(name);
				showErrorMessage(merchrTranslations.fieldsRequired);
				return false;
			}
			if(description.val() == '') {
				processRequiredField(description);
				showErrorMessage(merchrTranslations.fieldsRequired);
				return false;
			}
			if(price.val() == '') {
				processRequiredField(price);
				showErrorMessage(merchrTranslations.fieldsRequired);
				return false;
			}
			
			// Check if negative profit, if so checkbox is ticked
			if(negativeProfit) {
				if(!$(".featherlight-inner .accept_negative_profit").is(':checked')) {
					showErrorMessage(merchrTranslations.acceptNegativeProfit);
					return false;
				}
			}
			
			// Save data to product card
			
			// Form data
			$("#merchr-hub-product-id-" + id + " .product-title-field").val(name.val());
			$("#merchr-hub-product-id-" + id + " .product-description-field").val(description.val());
			$("#merchr-hub-product-id-" + id + " .product-price-field").val(price.val());
			if(negativeProfit) {
				$("#merchr-hub-product-edit-" + id + " .accept_negative_profit").prop('checked', true);
			}
			
			// Visual data
			$("#merchr-hub-product-id-" + id + " .merchr-hub-product-card-title h3").html(name.val());
			$("#merchr-hub-product-id-" + id + " .merchr-hub-product-card-description").html(description.val().substring(0,52) + '...');
			$("#merchr-hub-product-id-" + id + " .merchr-hub-product-card-price").html(price.val());
			
			// Successful
			$.featherlight.current().close();
			return false;
		});
		
		// Check for negative value
		var loadedProfit = parseFloat($(".featherlight-inner .product-markup-field").val()).toFixed(2);
		if(loadedProfit < 0) {
			$(".featherlight-inner .product-markup-field, .featherlight-inner .product-markup-percentage-field").css({'border-color' : '#cc0000', 'color' : '#cc0000'});
			$(".featherlight-inner .merchr-negative-profit-wrapper").show(0);
		}
		
		// Bind selling price onblur
		$(".featherlight-inner").on("blur", ".product-price-field", function(e) {
			var price = parseFloat($(this).val()).toFixed(2),
			cost = parseFloat($(".featherlight-inner .product-cost-field").val()).toFixed(2),
			profit = (price - cost).toFixed(2),
			profitPercentage = ((profit / cost) * 100).toFixed(2);
			
			// Update values
			$(".featherlight-inner .product-markup-field").val(profit);
			$(".featherlight-inner .product-markup-percentage-field").val(profitPercentage);
			
			// Check for negative
			if(profit < 0) {
				negativeProfit = true;
				$(".featherlight-inner .product-markup-field, .featherlight-inner .product-markup-percentage-field").css({'border-color' : '#cc0000', 'color' : '#cc0000'});
				$(".featherlight-inner .merchr-negative-profit-wrapper").show(0);
			} else {
				negativeProfit = false;
				$(".featherlight-inner .product-markup-field, .featherlight-inner .product-markup-percentage-field").css({'border-color' : '#cccccc', 'color' : '#a5a5a5'});
				$(".featherlight-inner .merchr-negative-profit-wrapper").hide(0);
			}
		});
	};
	
	
	/* 
	 * Bind Product Buttons
	 *
	 * @param string
	 * 
	 * Private
	*/
	var processImportNotice = function(msg) {
		var errorElement = $("#merchr-importer-error");
		errorElement.html("<span>" + msg + "</span>");
		setTimeout(function() {
			errorElement.find("span").fadeOut(200, function() {
				errorElement.html("");
			});
		}, 6000);
	};
	
	/* 
	 * Bind Product Buttons
	 * 
	 * Private
	*/
	var bindProductButtons = function() {
		$(".merchr-wrap").on("click", ".merchr-hub-btn-product-select", function(e) {
			e.preventDefault();
			
			var id = $(this).data('id'),
			altText = $(this).data('alt-text'),
			orignalText;
			
			// Check if already selected
			if($("#merchr-hub-product-id-" + id).hasClass("merchr-hub-active")) {
				$("#merchr-hub-product-id-" + id).removeClass("merchr-hub-active");
				$(this).html('SELECT').removeClass("merchr-hub-active");
			} else {
				orignalText = $(this).html();
				$("#merchr-hub-product-id-" + id).addClass("merchr-hub-active");
				$(this).html(altText).addClass("merchr-hub-active");
			}
			
			return false;
		});
		
		$(".merchr-hub-btn-product-edit").each(function(i) {
			var btn = $(this),
			id = btn.data("id");
			
			btn.featherlight({
				targetAttr: 'href',
				afterContent: function(e) {
					bindEditForm(id);
				},
			});
		});
		
		$(".merchr-wrap").on("click", ".merchr-hub-import-products-btn", function(e) {
			e.preventDefault();
			
			// Check we have products selected
			if($(".merchr-hub-active").length <= 0) {
				processImportNotice(merchrTranslations.noProductsSelected);
				return false;
			}
			
			// Process selected products
			var products = [], n = 0;
			$(".merchr-hub-active").each(function(i) {
				var ele = $(this);
				
				if(typeof ele.attr("id") === "undefined") {
					return;
				}
				
				var dataID = ele.data("id"),
				name = $("#merchr-hub-product-edit-" + dataID + " .product-title-field").val().replace(/"/g, '&quot;'),
				description = $("#merchr-hub-product-edit-" + dataID + " .product-description-field").val().replace(/"/g, '&quot;'),
                price = $("#merchr-hub-product-edit-" + dataID+ " .product-price-field").val();
				
                products.push( 
					{  
						id: dataID,
						name: encodeURIComponent(name),
						description: encodeURIComponent(description),
						price: encodeURIComponent(price)
					}
				);
				
				n++;
			});
			
			// Get nonce and referrer
			var nonce = $("#_nonce_import_products").val(),
			referrer = $('input[name="_wp_http_referer"]').val();
			
			// Get url
			var url = $("#merchr-import-url").val();
			
			// Show loader
			hub.showHideLoader();
			
			// Make AJAX request
			$.ajax({
				global: false,
				beforeSend: function(request) {
					request.setRequestHeader("App-Request-Type", defaults.header);
				},
				cache: defaults.cacheBool,
				timeout: 90000,
				type: defaults.requestType,
				url: url,
				data: '_nonce_import_products=' + nonce + "&_wp_http_referer=" + referrer + "&products=" + JSON.stringify(products),
				success: (function(data) {
					// Parse JSON Data
					data = JSON && JSON.parse(data) || $.parseJSON(data);
					
					// Hide loader
					hub.showHideLoader();
					
					// Check status
					if(data.status == 'success') {
						// Update page content
						hub.updatePageContent("#merchr-hub-product-live-content", data.payload);
					} else {
						// Show error notice
						processImportNotice(data.msg);
					}
					
					return true;
				}),
				error: (function(request, errorType, errorThrown) { 
					// Hide loader
					hub.showHideLoader();
					
					// Show notice
					processImportNotice(merchrTranslations.importFailed);
					return false;
				})
			});
			
			return false;
		});
		
	};
	
	/* 
	 * Bind Filter Selects
	 * 
	 * Private
	*/
	var bindFilterSelects = function() {
		$(".merchr-wrap").on("change", "#merchr-category-filter, #merchr-collection-filter, #merchr-industry-filter", function(e) {
			e.preventDefault();
			processFilterSelection(
				$("#merchr-category-filter").val(), 
				$("#merchr-collection-filter").val(), 
				$("#merchr-industry-filter").val()
			);
			return false;
		});
	};
	
	/* 
	 * Bind Reset Filter Button
	 * 
	 * Private
	*/
	var bindResetFilterButton = function() {
		$(".merchr-wrap").on("click", "#merchr-filter-reset-btn", function(e) {
			e.preventDefault();
			
			// Reset selects
			$("#merchr-category-filter, #merchr-collection-filter, #merchr-industry-filter").prop("selectedIndex", 0);
			
			// Restore all product cards
			$(".merchr-hub-product-card").removeClass("merchr-hub-product-hidden");
			
			return false;
		});
	};
	
	/* 
	 * Process Filter Selection
	 *
	 * @var mixed
	 * @var mixed
	 * @var mixed
	 * 
	 * Private
	*/
	var processFilterSelection = function(category, collection, industry) {
		// Check we have at least one value;
		if(category == '' && collection == '' && industry == '') {
			// Restore all product cards
			$(".merchr-hub-product-card").removeClass("merchr-hub-product-hidden");
			return;
		}
		
		// Check each product if matches filter(s)
		$(".merchr-hub-product-card").each(function(i) {
			var ele = $(this),
			cat = ele.data("categories").includes(category),
			col = ele.data("collections").includes(collection),
			ind = ele.data("industries").includes(industry);
			
			// Do check
			if(
				(!cat && category != '') ||
				(!col && collection != '') ||
				(!ind && industry != '')
			) {
				ele.addClass("merchr-hub-product-hidden");
			} else {
				ele.removeClass("merchr-hub-product-hidden");
			}
		});
	};
	
	/* 
	 * Wait for document ready
	*/
	$(document).ready(function() {
		if($(".merchr-hub-product-wrapper").length > 0) {
			bindProductButtons();
			bindFilterSelects();
			bindResetFilterButton();
		}
	});
})(jQuery, merchrHub);
