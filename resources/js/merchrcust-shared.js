jQuery(function($){
	
	// Only call if needed
    const fontCustomiserPresent = $("#merchrcust_fontfamily_select");
    
    if(fontCustomiserPresent.length > 0) {

        merchrcustFlatCanvas.init($);
        merchrcustPerspective.init($);
        selectFonts();

        function selectFonts(){
            $('.js__selectFonts select').each(function(){
                $(this).find('option').each(function(){
                    $(this).css({fontFamily: $(this).val()});
                });
            }).on('change set', function(){
                $(this).css({fontFamily: $(this).children(':selected').val()});
            }).trigger('set');
        }

        WebFont.load({
            google: {
                families: window.merchrcust_fontlist
            },
            fontinactive: function(familyName, fvd) {
                console.error(familyName + " font family can't be loaded.");
            },
            active: function() {
                merchrcustFlatCanvas.showTextOnAllImages();
                if (typeof merchrcustAdmin !== 'undefined'){
                    merchrcustAdmin.onFontsLoaded();
                }
                if (typeof merchrcustPublic !== 'undefined'){
                    merchrcustPublic.onFontsLoaded();
                }
            }
        });

    }

});

const merchrcustFlatCanvas = {
	
	timeouts: [],
	timeoutdelay: 250,
    isSingleProductImageProcessed: false,
	
	init($){
		const self = this;
		self.jQuery = $;
		self.initTimeoutDelay();
		self.hires = self.isRetinaDisplay();
		self.initDetectWindowResize();
	},
	
	initDetectWindowResize(){
        const self = this;
		const $ = jQuery;
		$(window).on('resize', function(){
            self.isSingleProductImageProcessed = false,
            self.merchrTriggerResize();
		});
	},
	
	initTimeoutDelay(){
		const self = this;
		if (self.isMobile()){
			self.timeoutdelay = 500;
		}else{
			self.timeoutdelay = 50;
		}
	},
	
	isMobile() {
		var check = false;
		(function(a){
		  if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) 
			check = true;
		})(navigator.userAgent||navigator.vendor||window.opera);
		return check;
	},
	
	isRetinaDisplay() {
		if (window.matchMedia) {
			var mq = window.matchMedia("only screen and (min--moz-device-pixel-ratio: 1.3), only screen and (-o-min-device-pixel-ratio: 2.6/2), only screen and (-webkit-min-device-pixel-ratio: 1.3), only screen  and (min-device-pixel-ratio: 1.3), only screen and (min-resolution: 1.3dppx)");
			return (mq && mq.matches || (window.devicePixelRatio > 1)); 
		}
    },
	
	merchrTriggerResize($els){
        const self = this;
		const $ = jQuery;
        let topOffsetAdjustment = 0;
        let leftpOffsetAdjustment = 0;
        let isSingleProductPage = false;
        
        if(typeof $els === "undefined") {
			// do all
			$els = $('.js__merchrAddText.js__merchrProductImage');
		}
        
        // Check product container for padding
        const productItem = $(".products .product_item").first();
        if ($(".products .product_item").length > 0) {
          topOffsetAdjustment = parseInt(productItem.css('padding-top')) + 2;
          leftpOffsetAdjustment = parseInt(productItem.css('padding-left'));
        }
        
        // Check for single product page
        if ($("body").hasClass("single-product")) {
          isSingleProductPage = true;
        }
        
		$els.each(function(){
            let $image = $(this).find('img');
			let $overlay = $(this).find('.merchrcust-overlay-canvas-image');
			let topOffset = topOffsetAdjustment;
            let leftOffset = leftpOffsetAdjustment;

            if(isSingleProductPage === true && self.isSingleProductImageProcessed === false) {
              topOffset = 0;
              leftOffset = 0;
              self.isSingleProductImageProcessed = true;
            }

            $overlay.css({
				width: $image.width(),
                height: $image.height(),
				margin: $image.css('margin'),
				padding: $image.css('padding'),
                top: topOffset + 'px',
                left: leftOffset + 'px'
			});
		});
	},
	
	drawImageFromRemote(sourceurl) {
		var img = new Image();
		var canvas = document.createElement('canvas');

		img.addEventListener("load", function() {
		   // The image can be drawn from any source
		   canvas.getContext("2d").drawImage(img, 0, 0, img.width, img.height, 0, 0, canvas.width, canvas.height);
		   // However the execution of any of the following methods will make your script fail
		   // if your image doesn't has the right permissions
		   canvas.getContext("2d").getImageData();
		   canvas.toDataURL();
		   canvas.toBlob();
		});

		img.setAttribute("src", sourceurl);
	},
	
	// placeImage
	setPlacedImage($canvas, image_url, options){
		const self = this;
		const $ = jQuery;
		options = Object.assign({
			remove_white: false // false | 'edges' | 'all'
		}, options);
		merchrcustPublic.workingShow();
		$canvas.each(function(){
			var id = this.id;
			var fcanvas = $(this).get(0).fcanvas;
			if (typeof fcanvas === 'undefined'){
				return;
			}
			self.removeImagesInFCanvas(fcanvas);
			var data = fcanvas.data;
			// image placement area is already stored
			self.createImageCanvas(image_url, data.abswidth, data.absheight, imageLoaded, options);
			function imageLoaded(datauri){
				var img = new Image;
				img.src = datauri;
				// we need to work out how to fit the image inside the available area
				//var scale_x = 
				//var scale_y = 
				var cimg = new fabric.Image(img, {
					angle: 0,
					width: data.abswidth, // constrains to this
					height: data.absheight, // constrains to this
					left: data.absleft,
					top: data.abstop,
					scaleX: 1, 
					scaleY: 1
				});

				fcanvas.add(cimg);
				self.updateCanvasToImage(fcanvas);
				merchrcustPublic.workingHide();
			};
		});
	},
	
	removeImagesInFCanvas(fcanvas){
		this.changeObjectsInFCanvas(fcanvas, 'image', 'remove');
	},
	
	hideImagesInFCanvas(fcanvas){
		this.changeObjectsInFCanvas(fcanvas, 'image', 'hide');
	},
	
	showImagesInFCanvas(fcanvas){
		this.changeObjectsInFCanvas(fcanvas, 'image', 'show');
	},
	
	hideTextInFCanvas(fcanvas){
		this.changeObjectsInFCanvas(fcanvas, 'text', 'hide');
	},
	
	showTextInFCanvas(fcanvas){
		this.changeObjectsInFCanvas(fcanvas, 'text', 'show');
	},
	
	changeObjectsInFCanvas(fcanvas, type, action){
		if (typeof fcanvas === 'undefined'){
			return;
		}
		var objects = fcanvas.getObjects();
		for (var i in objects){
			if (objects[i].isType(type)){
				switch (action){
					case 'hide':
						objects[i].opacity = 0;
						break;
					case 'show':
						objects[i].opacity = 1;
						break;
					case 'remove':
						fcanvas.remove(objects[i]);
						break;
				}
			}
		}
		
	},
	
	createImageCanvas(image_url, cwidth, cheight, callback, options){
		const self = this;
		const $ = jQuery;
		options = Object.assign({
			remove_white: false
		}, options);
		$('<img>', {
			crossOrigin: 'Anonymous',
			src: image_url
		}).on('load', function(){
			var img = this;
			var canvas = document.createElement('canvas');
			canvas.width = cwidth;
			canvas.height = cheight;
			var ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
			var fit = self.fitOneThingInsideAnother(cwidth, cheight, img.width, img.height);
			ctx.drawImage(img, fit.left, fit.top, fit.width, fit.height);
			var datauri = canvas.toDataURL();
			if (options.remove_white){
				//return merchrcustImageManipulation.removeWhite(datauri, callback, options.remove_white);
			}			
			return callback(datauri);
		});
	},
	
	getCanvasData(clone){
		const $ = jQuery;
		var data = $('#merchrcust_data').data('merchrcust');
		if (clone){
			return $.extend({}, data);
		}else{
			return data;
		}
	},
	
	setCanvasDataNode(key, value){
		const $ = jQuery;
		var data = this.getCanvasData();
		if (data){
			data[key] = value;
		}
		$('#merchrcust_data').data('merchrcust', data);
		if (merchrcustPublic && merchrcustPublic.restartCartThumbnailUpdateTask){
			merchrcustPublic.restartCartThumbnailUpdateTask();
		}
	},
	
	setTextProperties($canvas, properties){
		const self = this;
		const $ = jQuery;
		
        if ($canvas !== null) {
            $canvas.each(function(){
                var id = this.id;
                var fcanvas = $(this).get(0).fcanvas;
                if (typeof fcanvas === 'undefined'){
                    return;
                }
                var objects = fcanvas.getObjects();
                for (var i in objects){
                    if (objects[i].isType('text') || objects[i].isType('textbox')){
                        objects[i].set(properties);
                        fcanvas.fire('text:changed', {target: objects[i]});
                    }
                }
                fcanvas.renderAll();
                self.updateCanvasToImage(fcanvas);
            });
        }
	},
	
	setFontFamily($canvas, family){
		const $ = jQuery;
		this.setTextProperties($canvas, {
			fontFamily: family
		});
		this.setCanvasDataNode('font_family', family);
	},
	
	setTextColor($canvas, color){
		this.setTextProperties($canvas, {
			fill: color
		});
		this.setCanvasDataNode('color', color);
	},
	
	setText(text){
		const $ = jQuery;
		if (text){
			$('.js__merchrcustCustomTextInput').val(text);
			$('.js__merchrcustCustomTextText').text(text);
		}
		this.setCanvasDataNode('text', text);
		$('.merchrcanvas').each(function(){
			var id = this.id;
			var fcanvas = $(this).get(0).fcanvas;
			if (!fcanvas){
				return;
			}
			var currenttext;
			if (!text || !text.length){
				currenttext = fcanvas.data.text;
			}else{
				currenttext = text;
			}
			if (typeof fcanvas === 'undefined'){
				return;
			}
			var objects = fcanvas.getObjects();
			if(objects.length > 0) {
				for (var i in objects){
					if (typeof objects[i].isType === 'function') {
						if (objects[i].isType('text') || objects[i].isType('textbox')){
							objects[i].set({
								text: currenttext
							});
						}
						fcanvas.fire('text:changed', {target: objects[i]});
					}
				}
			}
		});
	},
	
	showTextOnAllImages(){
        const self = this;
		const $ = jQuery;
		if ($('body.single-product').length){
			var data = $('#merchrcust_data').data('merchrcust');
			if (data){
				// get the image
				var $img = $('.woocommerce-product-gallery__wrapper');
				var $wrapper = $('<div>').data('merchrcust', data).addClass('js__merchrAddText js__merchrProductImage');
				$img.wrap($wrapper);
			}
		}
        self.showTextOnAllImagesOnScroll();
	},
	
	showTextOnAllImagesOnScroll(){
		const self = this;
		const $ = jQuery;
		var viewport_top = $(window).scrollTop();
		var viewport_bottom = $(window).scrollTop() + $(window).height();
		var spillover = 500;
		$('body.single-product .js__merchrAddText.js__merchrProductImage').each(function(){
			var el_top = $(this).offset().top - spillover;
			var el_bottom = $(this).offset().top + $(this).outerHeight() + spillover;
			if (el_bottom < viewport_top || el_top > viewport_bottom){
				$(this).removeClass('merchrcust-touched');
				$(this).find('.merchrcust-overlay-canvas-image').remove();
				var canvas_id = $(this).data('canvas_id');
				if (canvas_id){
					$('#' + canvas_id).remove();
				}
				return;
			}
			if ($(this).is('.merchrcust-touched')){
				return;
			}
			$(this).addClass('merchrcust-touched');
			var data = $(this).data('merchrcust');
			var upscale = 2;
			if (self.hires){
				upscale = 4;
			}
			data.size = parseInt($(this).width()) * upscale;
			var $image = $('<img>').appendTo($(this)).addClass('merchrcust-overlay-canvas-image');
			var canvas_id = merchrcustFlatCanvas.overlayFabricText($image, data);
			$(this).data('canvas_id', canvas_id);
			if ($(this).is('.js__merchrProductImage')){
				merchrcustPublic.product_image_canvas_id = canvas_id;
			}
			//self.merchrTriggerResize($(this));
		});
	},

	overlayFabricText($image, data){
		const $ = jQuery;
		const self = this;
		//data.size = 500;
		// need to add a random element because the image can appear in more than one place,
		// for example product details and pagination
		var id = 'merchrcanvas' + data.post_id + '_' + Math.floor(Math.random() * 999999);
		// store the real canvas hidden on body
		var $wrapper = $('<div>').appendTo('body').hide();
		$('<canvas>', {id: id}).addClass('merchrcanvas').appendTo($wrapper);
		var canvas = new fabric.Canvas(id);
		
		// $image is where the canvas will show as an image
		canvas.$image = $image;
		canvas.id = id;
		
		// Store the canvas on the hidden element for later use
		document.getElementById(id).fcanvas = canvas;
		
		if (data.placement_editor_type === 'full'){
			// for now assume there's only one area, and it's rectangular
			data.offset_x = data.personalisation_areas[0].ptlx;
			data.offset_y = data.personalisation_areas[0].ptly;
			data.width = data.personalisation_areas[0].ptrx - data.personalisation_areas[0].ptlx;
			data.height = data.personalisation_areas[0].pbly - data.personalisation_areas[0].ptly;
		}
        data.absleft = data.size * data.offset_x / 100;
		data.abstop = data.size * data.offset_y / 100;
		data.abswidth = data.size * data.width / 100;
		data.absheight = data.size * data.height / 100;
		data.font_size = Math.round(0.08 * data.size);
		if (data.fixed_text){
			data.text = data.fixed_text;
		}else{
			data.text = self.getCurrentText(data.text);
		}
		canvas.data = data;
		canvas.setHeight(data.size);
		canvas.setWidth(data.size);
		canvas.on('text:changed', function(opt) {
			var target = opt.target;
			var text = target.get('text');
			var font_family = target.get('fontFamily');

            // Set absolute fixedHeight (add 10%)
            target.fixedHeight = canvas.data.absheight * 1.1;

            // create text using regular canvas to get its size
			// This measurement is expensive since it involves analysing the glyphs of text
			var context = canvas.getContext('2d');
            context.textBaseline = 'middle';
			var font_size = data.font_size; // start with default, don't bind the values
            do{
				font_size = font_size - 0.5;
				context.font = font_size + "px " + font_family;
			} while(
             context.measureText(text).width > target.fixedWidth 
             || (context.measureText(text).actualBoundingBoxAscent + context.measureText(text).actualBoundingBoxDescent) > target.fixedHeight
            );

            target.fontSize = font_size;
			// also centre text
			var textleft = data.absleft + ( ( target.fixedWidth - context.measureText(text).width ) / 2 ) + target.position_offset;
			// originY: 'center' aligns to v-centre, so all we need to do is place the text top to the center of the bounding box
			var texttop = data.abstop + ( data.absheight / 2 ) + target.position_offset;

			target.left = textleft;
			target.top = texttop;
			/*
            // but now we have to rotate, which changes everything
			var rotate_data = self.rotatedTextData(data.size, data.rotate, textleft, texttop);
			target.left = rotate_data.absleft;
			target.top = rotate_data.abstop;
			target.rotate(data.rotate);
			*/
			canvas.requestRenderAll();
			self.updateCanvasToImage(canvas);
		});
		
		if (data.bounding_rectangle){
			var rectangle = new fabric.Rect(self.createBoundingBoxData(data));
			rectangle.rotate(data.rotate);
		    canvas.add(rectangle); 
		}
		if (data.print_type === 'engrave'){
			self.canvasAddEngrave(canvas, data);
			data.text_color = '#AAAAAA';
		}
		var textline = new fabric.Text(data.text, self.createTextLineData(data));
		
		canvas.add(textline);
		canvas.fire('text:changed', {target: textline});
		
		//if (!data.img_src || data.img_src === ''){
		//	data.img_src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
		//}
		
		if (data.img_src){
			fabric.Image.fromURL(data.img_src, function(img) {
				//i create an extra var for to change some image properties
				canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
					scaleX: canvas.width / img.width,
					scaleY: canvas.height / img.height
				 });
			});
		}
		
		self.updateCanvasToImage(canvas);
		//self.merchrTriggerResize();
		data.canvas = canvas;
		data.textline = textline;
		return id;
	},
	
	/*
	 Redraw the image from what's on the canvas.
	 */
	updateCanvasToImage(canvas2d){
		const self = this;
		const $ = jQuery;
		clearTimeout(self.timeouts[canvas2d.id]);
		self.timeouts[canvas2d.id] = setTimeout(function(){
			var imgsrc = canvas2d.toDataURL('image/jpg');
			if (canvas2d.data.placement_editor_type === 'full'){
				// convert to 3D
				var callback = function(canvas3d){
					var imgsrc3d = canvas3d.toDataURL('image/jpg');
					canvas2d.$image.attr('src', imgsrc3d);
				};
				merchrcustPerspective.drawDistortedImage(canvas2d.data, imgsrc, callback);
			}else{
				canvas2d.$image.attr('src', imgsrc);
			}
		}, self.timeoutdelay);
	},
	
	/* Hide the preview and show the original image instead */
	hideCanvasPreview(canvas2d){
		var imgsrc = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
		canvas2d.$image.attr('src', imgsrc);
	},
	
	canvasAddEngrave(canvas, data){
		const $ = jQuery;
		const self = this;
		data.text_color = '#555555';
		var engravedata = $.extend(self.createTextLineData(data), {position_offset: -1});
		var textline2 = new fabric.Text(data.text, engravedata);
		canvas.add(textline2);
		canvas.fire('text:changed', {target: textline2});
	},
	
	createBoundingBoxData(data){
		return { 
			hasControls: false,
			lockMovementX: true,
			lockMovementY: true,
			width: data.abswidth, 
			height: data.absheight, 
			left: data.absleft, 
			top: data.abstop, 
			fill: 'rgba(255, 0, 0, 0.3)',
			strokeWidth: 1,
			stroke: '#00FF00'
		};
	},
	
	createTextLineData(data, offset){
		if(typeof offset === "undefined") {
			offset = 0;
		}
		return {
			width: data.abswidth,
			top: data.abstop + offset,
			left: data.absleft + offset,
			textAlign: 'center',
			fixedWidth: data.abswidth,
			fontFamily: data.font_family,
			editable: false,
			hasControls: false,
			lockMovementX: true,
			lockMovementY: true,
			fill: data.text_color,
			originY: 'center',
			position_offset: 0 // this is used for engraving
		};
	},
	
	getCurrentText(default_text){
		const self = this;
		// check URI 
		var urlp = getUrlParameter('mctext');
		if (urlp && typeof urlp !== 'undefined' && urlp !== 'undefined'){
			localStorage.setItem('merchrcust_customtext', urlp);
			self.setText(urlp);
			return urlp;
		}
		
		// check local storage
		var local = localStorage.getItem('merchrcust_customtext');
		if (local && typeof local !== 'undefined' && local !== 'undefined'){
			self.setText(local);
			return local;
		}

		return default_text;
	},
	
	fitOneThingInsideAnother(conw, conh, objw, objh){
		// first find out which way it will fit
		var conratio = conw / conh;
		var objratio = objw / objh;
		var objw_new, objh_new, left, top;
		if (conratio < objratio){
			//	+-------------+
			//	|             |
			//	|+-----------+|
			//	||           ||
			//	||           ||
			//	||           ||
			//	|+-----------+|
			//	|             |
			//	+-------------+
			objw_new = conw;
			objh_new = objh * ( conw / objw );
			left = 0;
			top = (conh - objw_new) / 2;
		}else{
			//	.____________________.
			//	|    +----------+    |
			//	|    |          |    |
			//	|    |          |    |
			//	|    |          |    |
			//	|    |          |    |
			//	|    |__________|    |
			//	+--------------------+
			objw_new = objw * ( conh / objh );
			objh_new = conh;
			left = (conw - objw_new) / 2;
			top = 0;
		}
		return {
			width: objw_new,
			height: objh_new,
			left: left,
			top: top
		};
	},
	
	/*
	 * Textline rotates from middle left, not center, so we need to fudge its posiiton
	 */
	rotatedTextData: function(size, angle, absleft, abstop) {
		const $ = jQuery;
		var math1 = $('#merchrcust_math1').val();
		var math2 = $('#merchrcust_math2').val();
		var math3 = $('#merchrcust_math3').val();
		var math4 = $('#merchrcust_math4').val();
		//console.log(math1, math2, math3, math4);
		var canvas_size = $('#merchrcust_canvas_size').val();
		absleft = parseFloat(absleft);
		abstop = parseFloat(abstop);
		var pcx = absleft / size * 100;
		var pcy = abstop / size * 100;
		var absleft_new = absleft;
		var abstop_new = abstop;
		//console.log('original', pcx, pcy);
		if (angle){
			// calculate the differences
			// https://docs.google.com/spreadsheets/d/1aRJlN0inMFIxwlLVKWZPbofGvCblfUKUHpgHuXLCHok/edit#gid=0
			// https://www.desmos.com/calculator/mkxmhlpdb4
			var dx = Math.sin(angle/115) * Math.sin(angle/115) * 36;
			var dy = Math.sin((angle/125) + 30) * Math.sin(angle/115) * 36;
			//var dx = Math.sin(angle/math1) * Math.sin(angle/math1) * math2;
			//var dy = Math.sin((angle/math3) + math4) * Math.sin(angle/math1) * math2;
			//console.log('dx,dy', dx, dy);
			var pcx_new = pcx + dx;
			var pcy_new = pcy + dy;
			var absleft_new = pcx_new / 100 * size;
			var abstop_new = pcy_new / 100 * size;
			//console.log('modified', pcx_new, pcy_new);
		}
		return {
			absleft: absleft_new,
			abstop: abstop_new
		};
	}

};


const merchrcustPerspective = {
	init($){
		const self = this;
		self.jQuery = $;
	},
	
	drawDistortedImage(data, imgsrc, callback){
		const self = this;
		const $ = jQuery;
		
		data = self.addAbsCoods(data);
		
		var image = new Image();
		//var size = $wrapper.width();
		var $wrapper = $('<div>');
		var canvas = self.createCanvas(data.size);
		$(image).attr('src', imgsrc).on('load', function() {
			self.drawTriangles(data, image);
		});
		$wrapper.append(canvas);
		self.context = canvas.getContext('2d');
        self.context.textBaseline = 'top';
		setTimeout(function(){
			callback(canvas);
			$wrapper.remove();
		}, 1);
	},
	
	createCanvas(size){
		if(typeof size === "undefined") {
			size = 500;
		}
		var canvas = document.createElement('canvas');
		canvas.setAttribute('width', size);
		canvas.setAttribute('height', size);
		return canvas;
	},
	
	drawTriangles(data, image){
		const self = this;
		self.context.clearRect(0, 0, data.size, data.size);
		var points = {};
		points.tl = new self.classes.Point(data.abscoords.atlx, data.abscoords.atly);
		points.tr = new self.classes.Point(data.abscoords.atrx, data.abscoords.atry);
		points.br = new self.classes.Point(data.abscoords.abrx, data.abscoords.abry);
		points.bl = new self.classes.Point(data.abscoords.ablx, data.abscoords.ably);

		var triangles = self.calculateGeometry(image, points);
		for (var triangle of triangles) {
			self.render(image, triangle, false); // change 1st arg to false to hide wireframe
		}
	},
	
	calculateGeometry(image, points) {
		const self = this;
		// clear triangles out
		var triangles = [];

		// generate subdivision
		var subs = 7; // vertical subdivisions
		var divs = 7; // horizontal subdivisions

		var dx1 = points.bl.x - points.tl.x;
		var dy1 = points.bl.y - points.tl.y;
		var dx2 = points.br.x - points.tr.x;
		var dy2 = points.br.y - points.tr.y;

		var imgW = image.naturalWidth;
		var imgH = image.naturalHeight;

		for (var sub = 0; sub < subs; ++sub) {
			var curRow = sub / subs;
			var nextRow = (sub + 1) / subs;

			var curRowX1 = points.tl.x + dx1 * curRow;
			var curRowY1 = points.tl.y + dy1 * curRow;

			var curRowX2 = points.tr.x + dx2 * curRow;
			var curRowY2 = points.tr.y + dy2 * curRow;

			var nextRowX1 = points.tl.x + dx1 * nextRow;
			var nextRowY1 = points.tl.y + dy1 * nextRow;

			var nextRowX2 = points.tr.x + dx2 * nextRow;
			var nextRowY2 = points.tr.y + dy2 * nextRow;

			for (var div = 0; div < divs; ++div) {
				var curCol = div / divs;
				var nextCol = (div + 1) / divs;

				var dCurX = curRowX2 - curRowX1;
				var dCurY = curRowY2 - curRowY1;
				var dNextX = nextRowX2 - nextRowX1;
				var dNextY = nextRowY2 - nextRowY1;

				var p1x = curRowX1 + dCurX * curCol;
				var p1y = curRowY1 + dCurY * curCol;

				var p2x = curRowX1 + (curRowX2 - curRowX1) * nextCol;
				var p2y = curRowY1 + (curRowY2 - curRowY1) * nextCol;

				var p3x = nextRowX1 + dNextX * nextCol;
				var p3y = nextRowY1 + dNextY * nextCol;

				var p4x = nextRowX1 + dNextX * curCol;
				var p4y = nextRowY1 + dNextY * curCol;

				var u1 = curCol * imgW;
				var u2 = nextCol * imgW;
				var v1 = curRow * imgH;
				var v2 = nextRow * imgH;

				var triangle1 = new self.classes.Triangle(
					new self.classes.Point(p1x, p1y),
					new self.classes.Point(p3x, p3y),
					new self.classes.Point(p4x, p4y),
					new self.classes.TextCoord(u1, v1),
					new self.classes.TextCoord(u2, v2),
					new self.classes.TextCoord(u1, v2)
				);

				var triangle2 = new self.classes.Triangle(
					new self.classes.Point(p1x, p1y),
					new self.classes.Point(p2x, p2y),
					new self.classes.Point(p3x, p3y),
					new self.classes.TextCoord(u1, v1),
					new self.classes.TextCoord(u2, v1),
					new self.classes.TextCoord(u2, v2)
				);

				triangles.push(triangle1);
				triangles.push(triangle2);
			}
		}
		return triangles;
	},

	
	render(image, tri, wireframe) {
		var self = this;
		
		if (wireframe) {
			self.context.beginPath();
			self.context.moveTo(tri.p0.x, tri.p0.y);
			self.context.lineTo(tri.p1.x, tri.p1.y);
			self.context.lineTo(tri.p2.x, tri.p2.y);
			self.context.lineTo(tri.p0.x, tri.p0.y);
			self.context.strokeStyle = '#ccc';
			self.context.stroke();
			self.context.closePath();
		}

		if (image) {
			self.drawTriangle(
				self.context, 
				image,
				tri.p0.x, tri.p0.y,
				tri.p1.x, tri.p1.y,
				tri.p2.x, tri.p2.y,
				tri.t0.u, tri.t0.v,
				tri.t1.u, tri.t1.v,
				tri.t2.u, tri.t2.v
			);
		}
	},
	
	// from http://tulrich.com/geekstuff/canvas/jsgl.js
	drawTriangle(ctx, im, x0, y0, x1, y1, x2, y2, sx0, sy0, sx1, sy1, sx2, sy2) {
		ctx.save();

		// Clip the output to the on-screen triangle boundaries.
		ctx.beginPath();
		ctx.moveTo(x0, y0);
		ctx.lineTo(x1, y1);
		ctx.lineTo(x2, y2);
		ctx.closePath();
		//ctx.stroke();//xxxxxxx for wireframe
		ctx.clip();

		/*
		ctx.transform(m11, m12, m21, m22, dx, dy) sets the self.context transform matrix.

		The self.context matrix is:

		[ m11 m21 dx ]
		[ m12 m22 dy ]
		[  0   0   1 ]

		Coords are column vectors with a 1 in the z coord, so the transform is:
		x_out = m11 * x + m21 * y + dx;
		y_out = m12 * x + m22 * y + dy;

		From Maxima, these are the transform values that map the source
		coords to the dest coords:

		sy0 (x2 - x1) - sy1 x2 + sy2 x1 + (sy1 - sy2) x0
		[m11 = - -----------------------------------------------------,
		sx0 (sy2 - sy1) - sx1 sy2 + sx2 sy1 + (sx1 - sx2) sy0

		sy1 y2 + sy0 (y1 - y2) - sy2 y1 + (sy2 - sy1) y0
		m12 = -----------------------------------------------------,
		sx0 (sy2 - sy1) - sx1 sy2 + sx2 sy1 + (sx1 - sx2) sy0

		sx0 (x2 - x1) - sx1 x2 + sx2 x1 + (sx1 - sx2) x0
		m21 = -----------------------------------------------------,
		sx0 (sy2 - sy1) - sx1 sy2 + sx2 sy1 + (sx1 - sx2) sy0

		sx1 y2 + sx0 (y1 - y2) - sx2 y1 + (sx2 - sx1) y0
		m22 = - -----------------------------------------------------,
		sx0 (sy2 - sy1) - sx1 sy2 + sx2 sy1 + (sx1 - sx2) sy0

		sx0 (sy2 x1 - sy1 x2) + sy0 (sx1 x2 - sx2 x1) + (sx2 sy1 - sx1 sy2) x0
		dx = ----------------------------------------------------------------------,
		sx0 (sy2 - sy1) - sx1 sy2 + sx2 sy1 + (sx1 - sx2) sy0

		sx0 (sy2 y1 - sy1 y2) + sy0 (sx1 y2 - sx2 y1) + (sx2 sy1 - sx1 sy2) y0
		dy = ----------------------------------------------------------------------]
		sx0 (sy2 - sy1) - sx1 sy2 + sx2 sy1 + (sx1 - sx2) sy0
	  */

		// TODO: eliminate common subexpressions.
		var denom = sx0 * (sy2 - sy1) - sx1 * sy2 + sx2 * sy1 + (sx1 - sx2) * sy0;
		if (denom === 0) {
			return;
		}
		var m11 = -(sy0 * (x2 - x1) - sy1 * x2 + sy2 * x1 + (sy1 - sy2) * x0) / denom;
		var m12 = (sy1 * y2 + sy0 * (y1 - y2) - sy2 * y1 + (sy2 - sy1) * y0) / denom;
		var m21 = (sx0 * (x2 - x1) - sx1 * x2 + sx2 * x1 + (sx1 - sx2) * x0) / denom;
		var m22 = -(sx1 * y2 + sx0 * (y1 - y2) - sx2 * y1 + (sx2 - sx1) * y0) / denom;
		var dx = (sx0 * (sy2 * x1 - sy1 * x2) + sy0 * (sx1 * x2 - sx2 * x1) + (sx2 * sy1 - sx1 * sy2) * x0) / denom;
		var dy = (sx0 * (sy2 * y1 - sy1 * y2) + sy0 * (sx1 * y2 - sx2 * y1) + (sx2 * sy1 - sx1 * sy2) * y0) / denom;

		ctx.transform(m11, m12, m21, m22, dx, dy);

		// Draw the whole image.  Transform and clip will map it onto the
		// correct output triangle.
		//
		// TODO: figure out if drawImage goes faster if we specify the rectangle that
		// bounds the source coords.
		ctx.drawImage(im, 0, 0);
		ctx.restore();
	},
	
	exampleImage(){
		// This is just image generation, skip to DATAURL: below
		var canvas2 =  document.createElement('canvas');
		var ctx = canvas2.getContext("2d");
        ctx.textBaseline = 'top';
		ctx.canvas.width  = 500;
		ctx.canvas.height = 800;

		// Just some example drawings
		var gradient = ctx.createLinearGradient(0, 0, 200, 100);
		gradient.addColorStop("0", "#ff0000");
		gradient.addColorStop("0.5" ,"#00a0ff");
		gradient.addColorStop("1.0", "#f0bf00");

		ctx.beginPath();
		ctx.moveTo(0, 0);
		for (var i = 0; i < 30; ++i) {
		  ctx.lineTo(Math.random() * 200, Math.random() * 100);
		}
		ctx.strokeStyle = gradient;
		ctx.stroke();
		ctx.font = '48px serif';
		var text = "Some text lalala";
		for (var i = 0; i < 20; i++){
			ctx.fillText(text, 10, 50 * i);
		}

		// DATAURL: Actual image generation via data url
		var target = new Image();
		target.src = canvas2.toDataURL();

		return target.src;
	},
	
	addAbsCoods(data){
		const self = this;
		data.abscoords = [];
		var allkeys = [
			'tlx', 
			'tly', 
			'trx', 
			'try', 
			'brx', 
			'bry', 
			'blx',
			'bly'
		];
		for (var i in allkeys){
			var akey = 'a' + allkeys[i];
			var pkey = 'p' + allkeys[i];
			data.abscoords[akey] = self.convertPercentToAbs(data.size, data.preview_coords[pkey]);
		}
		return data;
	},
	
	convertPercentToAbs(size, pc){
		return pc * size / 100;
	},

	classes: {
		Point: function(x,y) {
			this.x = ( x? x : 0 ) + 6;
			this.y = ( y? y : 0 ) + 6;
		},

		TextCoord: function(u,v) {
			this.u = u?u:0;
			this.v = v?v:0;
		},

		Triangle: function(p0, p1, p2, t0, t1, t2) {
			this.p0 = p0;
			this.p1 = p1;
			this.p2 = p2;

			this.t0 = t0;
			this.t1 = t1;
			this.t2 = t2;
		}
	},
	
};

if (typeof 'getUrlParameter' !== 'function'){
	function getUrlParameter(sParam) {
		var sPageURL = window.location.search.substring(1),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
			}
		}
		return false;
	};
}

if (typeof 'replaceUrlParam' !== 'function'){
	function replaceUrlParam(url, paramName, paramValue){
		var pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
		if (url.search(pattern)>=0) {
			return url.replace(pattern,'$1' + paramValue + '$2');
		}
		url = url.replace(/[?#]$/,'');
		if (paramValue && paramValue.length){
			return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
		}else{
			return url;
		}
		
	}
}