
jQuery(function($){
	merchrcustPublic.init(jQuery);
});

jQuery(window).on("load", function(){
	merchrcustPublic.initAfterWindowLoad();
});

const merchrcustPublic = {
	
	// - Text Stuff ------------------------------------------------------------

	local_storage_custom_key: 'merchrcust_customtext',
    local_storage_custom_colour_key: 'merchrcust_customtext_colour',
    local_storage_custom_font_key: 'merchrcust_customtext_font',
	cart_thumbnail_timer: false,
	querystring_custom_key: 'mctext',
	storage: {
		add_to_cart_triggered: false,
		add_to_cart_thumbnail_needs_updating: true, 
		customisation_on: null,
	},

	init($){
		var self = this;
		self.jQuery = $;
		self.hideCustomisationBeforeLoad();
		self.resetToDefaultTextButton();
		self.copyToClipboard();
		//self.moveQty();
	},
	
	initAfterWindowLoad(){
		// Variations load after page load, so we need to defer some things
		var self = this;
		self.createHiddenThumbnail();
		self.initCustomisationOption();
		self.initTextInput();
		self.initTextColorChange();
		self.initFontFamilyChange();
		self.initPlaceImage();
		self.showCustomisation();
		//self.addToCartHook();
		// don't need this. things are updated on load that trigger this self.restartCartThumbnailUpdateTask();
	},
	
	// TODO: change thumbnail image on variation change
	// TODO: sync thumbnail data every update, apart from size and background
	
	createHiddenThumbnail(){
		const $ = this.jQuery;
	},
	
	restartCartThumbnailUpdateTask(){
		const self = this;
		const $ = jQuery;
		self.storage.add_to_cart_thumbnail_needs_updating = true;
		// don't set a time out, just allow add to cart button to create the image. the creation doesn't take long.
		//$('.single_add_to_cart_button').prop('disabled', true);
		//clearTimeout(this.cart_thumbnail_timer);
		//this.cart_thumbnail_timer = setTimeout(function(){
		//	self.pushThumbnailToCartForm();
		//}, 500);
	},
	
	pushThumbnailToCartForm(target){
		const $ = jQuery;
		const self = this;
		$('.single_add_to_cart_button').prop('disabled', true);
		var thumb_id = 'merchr_customised_thumbnail';
		var callback = function(){
			var $field = $('#merchrcust_thumbnail_field');
			var stored = $field.val();
			var latest;
			if (!self.storage.customisation_on){
				// if no customisation set it to empty
				latest = '';
			}else{
				latest = $('#' + thumb_id).get(0).toDataURL('image/jpg');
			}
			if (!self.storage.customisation_on || stored !== latest){
				if($('#merchrcust_thumbnail_field').length > 0) {
					$('#merchrcust_thumbnail_field').one('triggerAddToCart', function(){
						if (self.storage.add_to_cart_triggered){
							self.storage.add_to_cart_triggered = false;
							self.storage.add_to_cart_thumbnail_needs_updating = false;
							setTimeout(function(){
								$('.single_add_to_cart_button').prop('disabled', false).trigger('click');
							}, 1);
						}
					}).val(latest).trigger('triggerAddToCart');
				} else {
					self.storage.add_to_cart_triggered = false;
					self.storage.add_to_cart_thumbnail_needs_updating = false;
					setTimeout(function(){
						$('.single_add_to_cart_button').prop('disabled', false).trigger('click');
					}, 1);
				}
			}
			self.storage.add_to_cart_thumbnail_needs_updating = false;
			$('.single_add_to_cart_button').prop('disabled', false);
		};
		
		
		var $prodcanvas = self.productDetailCanvas();
		if ($prodcanvas){
			var size = 250;
			$('#' + thumb_id).remove();
			var $thumb = $('<canvas>', {
				id: thumb_id,
			}).attr({
				width: size,
				height: size,
			}).css({
				width: size,
				height: size,
			}).hide().appendTo('form.cart');

			if (!self.storage.customisation_on){
				// if no customisation do no more
				return callback();
			}
			
			var ctx = $thumb.get(0).getContext('2d');

			var img = new Image;
			img.onload = function(){
				ctx.drawImage(this, 0, 0, size, size);
				var fcanvas = $prodcanvas.get(0).fcanvas;
				var img2 = new Image;
				img2.src = fcanvas.toDataURL('image/png');
				ctx.drawImage(img2, 0, 0, size, size);
				callback();
			};
			img.src = self.productDetailImage().src;
		} else {
			// if no customisation do no more
			return callback();
		}
		
	},
	
	addToCartHook(){
		const $ = jQuery;
		const self = this;
		$('form.cart').on('submit', function(e){
			if (self.storage.add_to_cart_thumbnail_needs_updating){
				self.storage.add_to_cart_triggered = true;
				e.preventDefault();
				self.pushThumbnailToCartForm();
			}
		});
	},
	
	hideCustomisationBeforeLoad(){
		const $ = jQuery;
		$('#merchrcust-fields-wrapper').hide();
		$('#merchrcust-fields-loading').show();
	},
	
	showCustomisation(){
		setTimeout(function() {
			const $ = jQuery;
			$('#merchrcust-fields-wrapper').show();
			$('#merchrcust-fields-loading').hide();
		}, 1);
	},

	moveQty(){
		const $ = jQuery;
		if (!$('body.single-product').length){
			return;
		}
		$('.quantity input').addClass('input-qty').appendTo('.js__merchrProductQtyInputHere');
	},

	onFontsLoaded(){
		//this.productDetailFabricText();
		this.detectVariationChange();
	},

	// get Product detail canvas 
	productDetailCanvas(){
		const self = this;
		const $ = jQuery;
		var prodcanvas = $('#' + self.product_image_canvas_id); // this ID is saved by merchrcustFlatCanvas.showTextOnAllImages
		if (prodcanvas.length){
			return prodcanvas;
		}
		return null;
		//return $('.js__merchrProductImage .merchrcanvas');
	},
	
	// get Product detail image 
	productDetailImage(){
		const $ = jQuery;
		var $images = $('.woocommerce-product-gallery__image img:not(.zoomImg)');
		if ($images.length === 1){
			return $images.get(0);
		}
		return $('.woocommerce-product-gallery__image.flex-active-slide img:not(.zoomImg)').get(0);
	},
	
	initPlaceImage(){
		const self = this;
		const $ = jQuery;
		$('#merchrcust-image-modal-button').on('click', function(e){
			e.preventDefault();
			self.openImageModal();
		});
		$('#merchrcust-image-place-button').on('click', function(e){
			e.preventDefault();
			merchrcustFlatCanvas.setPlacedImage(self.productDetailCanvas(), $('#merchrcust_image_to_place').val());
		});
		$('#merchrcust-remove-white-button').on('click', function(e){
			e.preventDefault();
			self.openRemoveWhiteModal();
		});
		$('#merchrcust-remove-white-none').on('click', function(e){
			e.preventDefault();
			merchrcustFlatCanvas.setPlacedImage(self.productDetailCanvas(), self.storage.placed_image_wrnone_url);
			$('#merchrcust_custom_image_whiteremove').val('none');
			self.closeRemoveWhiteModal();
		});
		$('#merchrcust-remove-white-edges').on('click', function(e){
			e.preventDefault();
			merchrcustFlatCanvas.setPlacedImage(self.productDetailCanvas(), self.storage.placed_image_wredges_url);
			$('#merchrcust_custom_image_whiteremove').val('edges');
			self.closeRemoveWhiteModal();
		});
		$('#merchrcust-remove-white-all').on('click', function(e){
			e.preventDefault();
			merchrcustFlatCanvas.setPlacedImage(self.productDetailCanvas(), self.storage.placed_image_wrall_url);
			$('#merchrcust_custom_image_whiteremove').val('all');
			self.closeRemoveWhiteModal();
		});
		$('.merchrcust-rwm-image-wrapper').on('click', function(e){
			e.preventDefault();
			$(this).parent().find('button').trigger('click');
		});
		window.addEventListener('message', self.receiveMessageFromIframe, false);
	},
	
	openImageModal(){
		const $ = jQuery;
		const self = this;
		self.workingShow();
		$('body').addClass('nobodyscroll');
		self.iframe_callback_id = 'merchr' + Math.floor( Math.random() * 99999999 ) + '' + Math.floor( Math.random() * 99999999 );
		var url = `${window.merchrcust_ext_images_url}&callback_id=${merchrcustPublic.iframe_callback_id}`;
		$('<iframe>', {
			id: 'merchrcust-image-handler-modal',
			src: url
		}).addClass('merchrcust-modal').appendTo('body').show();
	},
	
	openRemoveWhiteModal(){
		const $ = jQuery;
		const self = this;
		$('body').addClass('nobodyscroll');
		$('#merchrcust-white-removal-modal').show();
		$('.merchrcust-rwm-image-wrapper').empty();
		$('<img>', {
			src: self.storage.placed_image_wrnone_url
		}).appendTo('#merchrcust-remove-white-none-image');
		$('<img>', {
			src: self.storage.placed_image_wrall_url
		}).appendTo('#merchrcust-remove-white-all-image');
		$('<img>', {
			src: self.storage.placed_image_wredges_url
		}).appendTo('#merchrcust-remove-white-edges-image');
	},
	
	closeRemoveWhiteModal(){
		const $ = jQuery;
		const self = this;
		$('body').removeClass('nobodyscroll');
		$('#merchrcust-white-removal-modal').hide();
	},
	
	workingShow(){
		const $ = jQuery;
		const self = this;
		$('<div>', {
			id: 'merchrcust-working'
		}).appendTo('body').append('<div>Working...</div>');
	},
	
	workingHide(){
		const $ = jQuery;
		const self = this;
		$('#merchrcust-working').remove();
	},
	
	imageModalLoaded(){
		const $ = jQuery;
		const self = this;
		self.workingHide();
		$('#merchrcust-image-handler-modal').show();
	},
	
	closeImageModal(){
		const $ = jQuery;
		$('#merchrcust-image-handler-modal').remove();
		$('body').removeClass('nobodyscroll');
	},
	
	receiveMessageFromIframe(event){
		// nope const self = this;
		const self = merchrcustPublic;
		const $ = jQuery;
		if (typeof event.data.message_from !== 'undefined' && event.data.message_from !== 'merchrhub_image_handler'){
			//console.log('not my message');
			return;
		}
		// check id matches
		if (event.data.callback_id !== merchrcustPublic.iframe_callback_id){
			//console.log('not my callback_id', merchrcustPublic.iframe_callback_id, event.data.callback_id);
			return;
		}
		switch (event.data.action){
			case 'place_image':
				self.placeImageFromIframe(event);
				break;
			case 'close_modal':
				self.closeImageModal();
				break;
			case 'modal_loaded':
				self.imageModalLoaded();
				break;
		}
	},
	
	placeImageFromIframe(event){
		const self = merchrcustPublic;
		const $ = jQuery;
		self.storage.placed_image_wrnone_url = event.data.data.thumbnail_abs_url;
		self.storage.placed_image_wrall_url = event.data.data.thumbnail_wrall_abs_url;
		self.storage.placed_image_wredges_url = event.data.data.thumbnail_wredges_abs_url;
		self.storage.placed_image_id = event.data.data.upload_id;

		var image_src = self.storage.placed_image_wrnone_url;

		$('#merchrcust_custom_image').val(event.data.data.upload_id + '|' + image_src);
		merchrcustFlatCanvas.setPlacedImage(self.productDetailCanvas(), image_src);
		self.storage.placed_image_url = image_src;
		self.closeImageModal();
		if (!event.data.data.has_transparency){
			self.showWhiteRemovalOptions();
		}else{
			self.hideWhiteRemovalOptions();
		}
	},
	
	initTextColorChange(){
		const self = this;
		const $ = jQuery;
		const current = localStorage.getItem(self.local_storage_custom_colour_key);
        
        if (current !== null) {
            $('input[name="merchrcust_text_color"]').val(current);
            merchrcustFlatCanvas.setTextColor(self.productDetailCanvas(), current);
        }
        
        $('input[name="merchrcust_text_color"]').on('change', function(){
			const val = $(this).val();
            merchrcustFlatCanvas.setTextColor(self.productDetailCanvas(), val);
            localStorage.setItem(self.local_storage_custom_colour_key, val);
		});
	},

	/*
	 * When a variation is changed it updates the image, and we need to react to that
	 * Change the canvas
	 * Change the text colour on the product iomage canvas
	 * @returns {undefined} 
	 */
	detectVariationChange(){
		return;
		const self = this;
		const $ = jQuery;
		$('input.variation_id').on('change', function(){
			//self.productDetailFabricText();
			//var text_color = self.getVariationTextColor();
			//var font_family = self.getCurrentFontFamily();
			//merchrcustFlatCanvas.setTextColor(self.productDetailCanvas(), text_color);
			//merchrcustFlatCanvas.setFontFamily(self.productDetailCanvas(), font_family);
		});
	},
	
	getVariationTextColor(){
		const self = this;
		const $ = jQuery;
		var variation_id = $('input.variation_id').val();
		// get the colour from the merchcust data if it exists ( set in product detail > variations ) 
		var data = $('#merchrcust_data').data('merchrcust');
		if (variation_id in data.variation_colors){
			return data.variation_colors[variation_id];
		}
		// otherwise return the default that's set on product detail > customisation
		return data.text_color;
	},
	
	getCurrentFontFamily(){
		const $ = jQuery;
        return $('#merchrcust_fontfamily_select').val();
	},

	/*
	 * Textarea fits to width and centres text but it allows line breaks
	 * Text doesn't allow line breaks, but doesn't centre itself
	 * 
	 * Create a div with its background as the original product image and plop the canvas with text inside that
	 * 
	 */
	//productDetailFabricText(){
	//	return;
	//	const $ = jQuery;
	//	if (!$('body.single-product').length){
	//		return;
	//	}
	//
	//	var data = $('#merchrcust_data').data('merchrcust');
	//	if (!data){
	//		return;
	//	}
	//	// get the image
	//	var $img = $('.woocommerce-product-gallery__wrapper .wp-post-image');
	//	var $gallery = $('.woocommerce-product-gallery');
	//	// don't put it as the background data.img_src = $img.attr('src');
	//	data.size = parseInt($gallery.width());
	//	$gallery.hide();
	//	var merchrgalleryid = 'merchr-product-gallery';
	//	$('#' + merchrgalleryid).remove();
	//	var $merchrgallery = $('<div>', {
	//		id: merchrgalleryid
	//	}).addClass('woocommerce-product-gallery').insertBefore($gallery);
	//	var $imgwrapper = $('<div>').addClass('merchrcust-image-canvas-wrapper').css({backgroundImage: 'url(' + $img.attr('src') + ')', height: data.size, width: data.size}).appendTo($merchrgallery);
	//	var $image = $('<img>').appendTo($imgwrapper);
	//	merchrcustFlatCanvas.overlayFabricText($image, data);
	//},
	
	initTextInput(){
		const $ = jQuery;
		const self = this;
		$('.js__merchrcustCustomTextInput').on('keyup change blur', function(e){
			var val = $(this).val();
			localStorage.setItem(self.local_storage_custom_key, val);
			if (e.type !== 'keydown' && e.type !=='keyup'){
				// trim but not while typing
				val = $.trim(val);
			}else{
				self.updateQueryString();
			}
			merchrcustFlatCanvas.setText(val);
		});
		
		var current = localStorage.getItem(self.local_storage_custom_key);
		if (current !== '') {
			$('.js__merchrcustCustomTextInput').val(current);
		}
	},
	
	initCustomisationOption(){
		const self = this;
		const $ = jQuery;
		$('#merchrcust-customisation-select input').on('click onInit', function(){
			var checked = $('#merchrcust-customisation-select input:checked').val();
			$('.merchrcust-fields').toggle(checked !== 'none');
			$('.merchrcust-fields-customisation').hide();
			$('.merchrcust-fields-customisation__' + checked).show();
			var prodcanvas = self.productDetailCanvas();
			if (prodcanvas){
				var fcanvas = prodcanvas.get(0).fcanvas;
				if (checked !== 'none'){
					self.storage.customisation_on = true;
					merchrcustFlatCanvas.updateCanvasToImage(fcanvas);
					if (checked === 'image'){
						merchrcustFlatCanvas.hideTextInFCanvas(fcanvas);
						merchrcustFlatCanvas.showImagesInFCanvas(fcanvas);
					}else{
						merchrcustFlatCanvas.hideImagesInFCanvas(fcanvas);
						merchrcustFlatCanvas.showTextInFCanvas(fcanvas);
					}
				}else{
					merchrcustFlatCanvas.hideCanvasPreview(fcanvas);
					self.storage.customisation_on = false;
				}
			}
		}).trigger('onInit');
	},
	
	initFontFamilyChange(){
		const self = this;
		const $ = jQuery;
		const current = localStorage.getItem(self.local_storage_custom_font_key);
        
        if (current !== null) {
            $('#merchrcust_fontfamily_select').val(current);
            merchrcustFlatCanvas.setFontFamily(self.productDetailCanvas(), current);
        }
        
        $('#merchrcust_fontfamily_select').on('change',function(){
			var prodcanvas = self.productDetailCanvas();
			var style = $(this).find('option:selected').attr('style');
			$(this).attr('style', style);
			if (prodcanvas){
				merchrcustFlatCanvas.setFontFamily(self.productDetailCanvas(), $(this).val());
                localStorage.setItem(self.local_storage_custom_font_key, $(this).val());
			}
		}).trigger('change');
	},
	
	updateQueryString(){
		const self = this;
		var text = localStorage.getItem(self.local_storage_custom_key);
		var newurl = replaceUrlParam(window.location.href, self.querystring_custom_key, text);
		history.replaceState({}, '', newurl);
	},
	
	resetToDefaultTextButton(){
		const $ = jQuery;
		const self = this;
		$('.js__merchrcustResetText').on('click', function(e){
			e.preventDefault();
			self.resetCustomTextInput();
		});
	},
	
	resetCustomTextInput(){
		const $ = jQuery;
		const self = this;
		localStorage.setItem(self.local_storage_custom_key, '');
		self.updateQueryString();
		window.location.reload();
	},
	
	copyToClipboard(){
		const $ = jQuery;
		const self = this;
		$('.js__copyToClipboardFromUrl').on('click', function(e){
			e.preventDefault();
			copyTextToClipboard(window.location.href);
			self.tinyNotice(e, 'Copied');
		});
	},
	
	showWhiteRemovalOptions(){
		const $ = jQuery;
		$('#merchrcust-remove-white-button').show();
	},
	
	hideWhiteRemovalOptions(){
		const $ = jQuery;
		$('#merchrcust-remove-white-button').hide();
	},

	// -------------------------------------------------------------------------
	
	tinyNotice(e, text){
		const $ = jQuery;
		var $notice = $('<div>').addClass('tinynotice').text(text).appendTo('body').css({position: 'absolute', top: e.pageY - 30, left: e.pageX, zIndex:9999});
		setTimeout(function(){
			$notice.remove();
		}, 1000);
	}
	
};

if (typeof 'copyTextToClipboard' !== 'function'){
	function copyTextToClipboard(text) {
		const el = document.createElement('textarea');
		el.value = text;
		el.setAttribute('readonly', '');
		el.style.position = 'absolute';
		el.style.left = '-9999px';
		document.body.appendChild(el);
		el.select();
		document.execCommand('copy');
		document.body.removeChild(el);
	}
}
