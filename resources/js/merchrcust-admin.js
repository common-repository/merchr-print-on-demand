jQuery(function($){
	
	merchrcustAdmin.init($);

});

const merchrcustAdmin = {
	
	init($){
		const self = this;
		self.jQuery = $;
		
		self.triggerProductTab();
		self.codeToTextarea();
		self.productTabPrintType();
		self.placementEditorType();
		self.detectProductImageChange();
		self.colorPicker();
	},
	
	detectProductImageChange(){
		const $ = jQuery;
		const self = this;
		
		var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
		var observer = new MutationObserver(function(mutations, observer) {
			// Fires every time a mutation occurs...
			mutations.forEach(function(mutation) {
				for (var i = 0; i < mutation.addedNodes.length; ++i) {
					if ( mutation.addedNodes[i].getElementsByTagName('img').length > 0) {
						self.productEditPreview();
					}
				}
			});
		});

		// define what element should be observed (#postimagediv is the container for the featured image)
		// and what types of mutations trigger the callback
		var $element = $('#postimagediv');
        if ($element.length > 0) {
		  var config = { subtree: true, childList: true, characterData: true };
		  observer.observe( $element[0], config );
		}
	},
	
	triggerProductTab(){
		const $ = jQuery;
		var hash = window.location.hash;
		if (hash){
			$('a[href="' + hash + '"]').trigger('click');
		}
	},
	
	placementEditorType(){
		const $ = jQuery;
		$('.js__merchrPlacementEditorType select').on('change triggerNow', function(){
			var $simple = $('.js__merchrUsesSimpleEditor');
			var $full = $('.js__merchrUsesFullEditor');
			var $everything = $('#merchrcust-editor');

			$everything.show();
			// hidden THEN show, because some fields are in both
			switch ($(this).val()){
				case 'none':
					$everything.hide();
					break;
				case 'simple':
					$full.hide();
					$simple.show();
					break;
				case 'full':
					$simple.hide();
					$full.show();
					break;
			}
		}).trigger('triggerNow');
	},
	
	productTabPrintType(){
		const $ = jQuery;
		$('.js__merchrPrintTypeSelect select').on('change triggernow', function(){
			if ($(this).val() === 'single'){
				$('.js__merchrTextColorInput').show();
			}else{
				$('.js__merchrTextColorInput').hide().find('input').val('');
			}
		}).trigger('triggernow');
	},
	
	codeToTextarea(){
		const $ = jQuery;
		$('.js__codeToTextarea').each(function(){
			var $textarea = $(this).find('textarea:first');
			$(this).find('code').css({cursor: 'pointer'}).on('click', function(){
				var caret_pos = $textarea.get(0).selectionStart;
				var textarea_text = $textarea.val();
				var text_to_add = $(this).text();
				$textarea.val(textarea_text.substring(0, caret_pos) + text_to_add + textarea_text.substring(caret_pos) );
				$textarea.focus().get(0).selectionStart = caret_pos + text_to_add.length;
				$textarea.focus().get(0).selectionEnd = caret_pos + text_to_add.length;
			});
		});
	},

	onFontsLoaded(){
		this.productEditPreview();
	},
	
	productEditPreview(){
		const $ = jQuery;
		var $wrapper = $('#merchrcust-editproduct-preview');
		if (!$wrapper.length){
			return;
		}
		$wrapper.empty();
		$('#set-post-thumbnail img').clone().appendTo($wrapper);
		$('#merchrcust_custom_product_data').find('input, select, textarea').on('change keyup triggernow', function(){
			var text_color = $('#merchrcust_text_color').val();
			if (!text_color.length) {
				text_color = 'black';
			}
			var get_from_ids = [
				'placement_editor_type',
				'offset_x',
				'offset_y',
				'width',
				'height',
				'font_family',
				'print_type',
				'rotate',
			];
			var data = {};
			for (var i in get_from_ids){
				var key = get_from_ids[i];
				var selector = '#merchrcust_' + key;
				data[key] = $(selector).val();
			}
			data = Object.assign(data, {
				preview_coords: $('#merchrcust_preview_coords').val()? JSON.parse($('#merchrcust_preview_coords').val()) : '',
				personalisation_areas: $('#merchrcust_personalisation_areas').val()? JSON.parse($('#merchrcust_personalisation_areas').val()) : '',
				size: $wrapper.width(),
				text_color: text_color,
				fixed_text: 'Sample Text',
				//img_src: $('#postimagediv img').attr('src'),
				bounding_rectangle: true,
			});
			var $image = $('<img>').appendTo($wrapper).addClass('merchrcust-overlay-canvas-image').css({position: 'absolute', top: 0, left: 0});
			merchrcustFlatCanvas.overlayFabricText($image, data);
			$wrapper.find('.merchrcust-overlay-canvas-image:not(:last)').remove();
		}).trigger('triggernow');
	},

	colorPicker() {
		/*
        const $ = jQuery;
		$('#merchrcust_text_color').spectrum({
			showInitial: true, 
			showInput: true, 
			preferredFormat: 'hex',
			change: function(color){
				$('#merchcust_text_color').val(color.toHexString());
			}
		});
        */
	}
};
