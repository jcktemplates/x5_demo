var insertThumbnails = function($element, ui, content, updateOld) {

	//X5 v9 - we must use "x5engine.imShowBox.Show()"
	if (x5Version == 9)
	{
		var anchorFunction  = 'x5engine.imShowBox.Show';
		var mediaType	    = 'img';
		var descriptionType = 'text';
	}
	//X5 v10 - we must use "x5engine.imShowBox()"
	else if (x5Version == 10)
	{
		var anchorFunction  = 'x5engine.imShowBox';
		var mediaType	    = 'image';
		var descriptionType = 'description';
	}

    // Set width
    var pageWidth = GridController.pageGrid.width,
        pageWidthSmaller = (parseInt(pageWidth.replace('px', ''))-6) + 'px',
        thumbnailWidthBig = ((content.settings.rowsPrFrame) ? ' style="width: '+pageWidth+'"' : ''),
        thumbnailWidthSmall = ((content.settings.rowsPrFrame) ? ' style="width: '+pageWidthSmaller+'"' : '');

	/*
	 *
	 * If rowsPrFrame is set, we must structure our HTML
	 * in a different way, than if it is not set.
	 * 
	 */
	if (content.settings.rowsPrFrame)
	{
		var thumbnails = '<div class="imRunner">\
							<div class="imSPage"' + thumbnailWidthBig + '>',
			media = [],
			i = 0;

		for (image in content.images)
		{
			i++;

			if (content.settings.showbox)
			{
				thumbnails += '<a href="gallery/' +  content.images[image].fileName + '" style="width:auto;height:auto;" onclick="return ' + anchorFunction + '(showbox_' + content.UUID + ', ' + image + ', this)">\
									<img style="display: block; float: left;" src="gallery/' + content.images[image].thumbFileName + '" alt=""/>\
								</a>';
			} else {
				thumbnails += '<img style="display: block; float: left;" src="gallery/' + content.images[image].thumbFileName + '" width="' + content.images[image].width + '" height="' + content.images[image].height + '" alt=""/>';
			}

			if (i >= content.settings.rowsPrFrame && image < (content.images.length - 1))
			{
				i = 0;
				thumbnails += '</div><div class="imSPage"' + thumbnailWidthBig + '>';
			}

            media.push('{type: "' + mediaType + '", url: "gallery/' +  content.images[image].fileName + '", ' + descriptionType + ': "", "effect": "none"}');

		}

		thumbnails += '</div>';

	} else {

		var thumbnails = '<div style="width: 100%; float: left; margin-bottom: 10px;">',
			media = [];


		//console.log('percent pr. image: ' + (100 / content.settings.visibleThumbnails));

		for (image in content.images)
		{
                    
			if (content.settings.showbox)
			{
				thumbnails += '<a href="gallery/' +  content.images[image].fileName + '" style="width:auto;height:auto;" onclick="return ' + anchorFunction + '(showbox_' + content.UUID + ', ' + image + ', this)">\
									<img style="display: block; float: left; max-width: ' + (content.settings.parentWidth / content.settings.visibleThumbnails) + 'px;" src="gallery/' + content.images[image].thumbFileName + '" alt=""/>\
								</a>';
			} else {
				thumbnails += '<img style="display: block; float: left;" src="gallery/' + content.images[image].thumbFileName + '"  height="' + content.images[image].height + '" alt=""/>';
			}

			if ((parseInt(image, 10) + 1) % content.settings.visibleThumbnails == 0)
			{
				thumbnails += '</div><div style="width: 100%; float: left;">';
			}


                        media.push('{type: "' + mediaType + '", url: "gallery/' +  content.images[image].fileName + '", ' + descriptionType + ': "", "effect": "none"}');

		}

		thumbnails += '</div>';

	}

    visibleThumbnailsAttr = ' data-visible-thumbnails="' + content.settings.visibleThumbnails + '"';
    thumbnailCellClass = ((content.settings.rowsPrFrame) ? 'imCellSPage' : '');
    thumbnailCellStyleClass = ((content.settings.rowsPrFrame) ? 'imCellStyleSPage' : '');
	thumbnailGallery = '<script type="text/javascript">\n\
									var showbox_' + content.UUID + ' = {\n\
										effect: "fade",\n\
										background: "#000000",\n\
										textColor: "#000000",\n\
										boxColor: "#FFFFFF",\n\
										startIndex: 0,\n\
										loadingImg: "res/imLoad.gif",\n\
										closeImg: "res/imClose.png",\n\
										media:[\n\
											' + media.join(',\n').replace(/(^,)|(,$)/g, '') + '\n\
										]};\n' 
										

									+ ((content.settings.rowsPrFrame) ? '$(document).ready(function() {\n\
												new x5engine.imSlider.Page("#imObjectGallery_' + content.UUID + '");\n\
											});' : '') +

							   '</script>\n\
								<script type="text/xml">\n\
									var showbox_' + content.UUID + ' = {\n\
										effect: "fade",\n\
										background: "#000000",\n\
										textColor: "#000000",\n\
										boxColor: "#FFFFFF",\n\
										startIndex: 0,\n\
										loadingImg: "res/imLoad.gif",\n\
										closeImg: "res/imClose.png",\n\
										media:[\n\
											' + media.join(',\n').replace(/(^,)|(,$)/g, '') + '\n\
										]};\n'

									+ ((content.settings.rowsPrFrame) ? '$(document).ready(function() {\n\
													new x5engine.imSlider.Page("#imObjectGallery_' + content.UUID + '");\n\
												});' : '') +


								'</script>\
							<div id="imObjectGallery_' + content.UUID + '" class="' + ((content.settings.rowsPrFrame) ? 'imSPageContainer' : '') + '"' + visibleThumbnailsAttr + thumbnailWidthBig + '>\
							' + thumbnails;

	if (typeof updateOld === 'undefined')
	{
		ui.draggable.remove();
	    GridController.convertPlaceholderToNormal();
	    //GridController.getNewCell().append(thumbnailGallery);
	    newCell = GridController.getNewCell();
	    
        newCell.closest('[id^="imCell_"]').attr('id', 'imCell_' + content.UUID);
        
        // Check if there should be a rowsprframe class
	    if (thumbnailCellClass != '' && ! newCell.closest('[id^="imCell_"]').hasClass(thumbnailCellClass)) {
            newCell.closest('[id^="imCell_"]').addClass(thumbnailCellClass);
        } else if ( thumbnailCellClass == '' && newCell.closest('[id^="imCell_"]').hasClass('imCellSPage') ) {
            newCell.closest('[id^="imCell_"]').removeClass('imCellSPage');
        }
        
	    if (thumbnailCellStyleClass != '' && ! newCell.closest('[id^="imCellStyle_"]').hasClass(thumbnailCellStyleClass)) {
            newCell.closest('[id^="imCellStyle_"]').addClass(thumbnailCellStyleClass);
        } else if ( thumbnailCellStyleClass == '' && newCell.closest('[id^="imCellStyle_"]').hasClass('imCellStyleSPage') ) {
            newCell.closest('[id^="imCellStyle_"]').removeClass('imCellStyleSPage');
        }
        
	    newCell.closest('[id^="imCellStyleGraphics_"]').attr('id', 'imCellStyleGraphics_' + content.UUID);
	    newCell.closest('[id^="imCellStyle_"]').attr('id', 'imCellStyle_' + content.UUID);
	    newCell.append(thumbnailGallery);
	    GridController.markNewCellAsOld();

	    $Thumbnailgallery = makeThumbnailGalleryDraggable($('<a />').addClass('ThumbnailgalleryDrag').data('Thumbnailgallery', 1).css({ color: '#000000' }).html('<img src="img/icons/iconThumbnailsGallery.png" style="height: 16px; margin-right: 12px; margin-left: 7px;" />Thumbnail gallery'));
		$('div.widget-options-container > div.insertThumbnailContainer').html($Thumbnailgallery);

	} else {
        // Check if there should be a rowsprframe class
	    if (thumbnailCellClass != '' && ! updateOld.closest('[id^="imCell_"]').hasClass(thumbnailCellClass)) {
            updateOld.closest('[id^="imCell_"]').addClass(thumbnailCellClass);
        } else if ( thumbnailCellClass == '' && updateOld.closest('[id^="imCell_"]').hasClass('imCellSPage') ) {
            updateOld.closest('[id^="imCell_"]').removeClass('imCellSPage');
        }
        
	    if (thumbnailCellStyleClass != '' && ! updateOld.closest('[id^="imCellStyle_"]').hasClass(thumbnailCellStyleClass)) {
            updateOld.closest('[id^="imCellStyle_"]').addClass(thumbnailCellStyleClass);
        } else if ( thumbnailCellStyleClass == '' && updateOld.closest('[id^="imCellStyle_"]').hasClass('imCellStyleSPage') ) {
            updateOld.closest('[id^="imCellStyle_"]').removeClass('imCellStyleSPage');
        }
        
		updateOld.html(thumbnailGallery);
	}

    // Set height of gallery
    calculateThumbnailsHeight($('#imObjectGallery_' + content.UUID), function(galleryHeight) {
        $('#imObjectGallery_' + content.UUID).css('height', galleryHeight + 'px');
        $('#imObjectGallery_' + content.UUID).closest('[id^="imCellStyle_"]').css('min-height', galleryHeight + 'px');
        $('#imObjectGallery_' + content.UUID).closest('[id^="imCell_"]').css('min-height', galleryHeight + 'px');
    });

    setTimeout(function() {

        // Adjust pagemenu height
        GridController.adjustPageMenuHeight();   

        // Adjust content wrapper height
        adjustPageHeight();

    }, 50);

};

/**
 * calculateThumbnailsHeight
 * 
 * Calculate height of thumbnail gallery based on the position of
 * the thumbnail images and run callback when height is found
 * 
 * @param object $element Gallery element
 * @param function callback Callback function to run when thumbnail gallery height is found
 */
var calculateThumbnailsHeight = function($element, callback) {
    
    var galleryTopPos = $element.offset().top,
        lowermostBottomPosImage = 0,
        notLoadedElements = Array();

    $element.find('img').each(function() {

        notLoadedElements.push(this);
        
        // Find img height when img is loaded
        $(this).one('load', function() {

            bottomPos = $(this).offset().top + $(this).height() - galleryTopPos;
            if (bottomPos > lowermostBottomPosImage) lowermostBottomPosImage = bottomPos;
            
            notLoadedElements.splice(notLoadedElements.indexOf(this), 1);
            
            // If all images are loaded, run callback function with height found
            if (notLoadedElements.length === 0) {
                callback(Math.ceil(lowermostBottomPosImage));
            }

        });

    });

};