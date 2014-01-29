insertWOWSlider = function($element, ui, content, updateOld) {

	var slideshowImages  = '',
		slideshowBullets = '',
		dimensions = content.settings.dimensions.split('x');
		
	for (image in content.images)
	{
		if (content.images[image].url != 'http://')
		{
			slideshowImages  += '<li><a href="' + content.images[image].url + '"><img style="max-height:' + String(dimensions[1]) + 'px;" src="img/wowslider/images/' + content.UUID + '/scaled/' + content.images[image].fileName + '?noCache=' + new Date().getTime() + '" alt="' + content.images[image].title + '" title="' + content.images[image].title + '" /></a>' + content.images[image].description + '</li>';
		} else {
			slideshowImages  += '<li><img data-width="' + content.images[image].width + '" data-height="' + content.images[image].height + '" style="max-height:' + String(dimensions[1]) + 'px;" src="img/wowslider/images/' + content.UUID + '/scaled/' + content.images[image].fileName + '?noCache=' + new Date().getTime() + '" alt="' + content.images[image].title + '" title="' + content.images[image].title + '" />' + content.images[image].description + '</li>';
		}
		slideshowBullets += '<a href="#" title="' + content.images[image].title + '"><img src="img/wowslider/images/' + content.UUID + '/tooltips/' + content.images[image].fileName + '" alt="' + content.images[image].title + '" />' + (image + 1) + '</a>';
	}

	if (content.settings.theme == 'block')
	{
		var textPrev = 'prev',
			textNext = 'next';
	} else {
		var textPrev = '',
			textNext = '';
	}

	
	var htmlvar = '<div class="WOWSliderRemoveBeforeSave">\
						<div class="wowslider-container_' + content.settings.theme + '" id="wowslider-container_' + content.UUID + '" style="max-width:' + String(dimensions[0]) + 'px; max-height:' + String(dimensions[1]) + 'px;">\
						<div class="ws_images">\
							<ul>\
								' + slideshowImages + '\
							</ul>\
						</div>\
						<div class="ws_bullets">\
							<div>\
								' + slideshowBullets + '\
							</div>\
						</div>\
						<div class="ws_shadow"></div>\
					</div>\
					<style>\
						#wowslider-container_' + content.UUID + ' { \
							max-width:' + String(dimensions[0]) + 'px;\
							max-height:' + String(dimensions[1]) + 'px;\
						}\
						* html #wowslider-container_' + content.UUID + ' { width:' + String(dimensions[0]) + 'px }\
					</style>\
					</div>';	

	var htmlvarpseudo = '<div class="WOWSliderRemoveBeforeSave">\
						<div class="wowslider-container_' + content.settings.theme + '" id="wowslider-container_' + content.UUID + '" style="max-width:' + String(dimensions[0]) + 'px; max-height:' + String(dimensions[1]) + 'px;">\
						<div class="ws_images">\
							<ul>\
								' + slideshowImages + '\
							</ul>\
						</div>\
						<div class="ws_bullets">\
							<div>\
								' + slideshowBullets + '\
							</div>\
						</div>\
						<div class="ws_shadow"></div>\
					</div>\
					<style>\
						#wowslider-container_' + content.UUID + ' { \
							max-width:' + String(dimensions[0]) + 'px;\
							max-height:' + String(dimensions[1]) + 'px;\
						}\
						* html #wowslider-container_' + content.UUID + ' { width:' + String(dimensions[0]) + 'px }\
					</style>\
					</div>';	


	var scriptvarCurrent = '<script id="wowslider-script_' + content.UUID + '">\
								var tester = ' + "'" + htmlvarpseudo + "'" + ';\
								$(tester).insertBefore($("#wowslider-script_' + content.UUID + '"));\
								jQuery("#wowslider-container_' + content.UUID + '").wowSlider({\
									effect: "' + String(content.settings.transistionEffect) + '",\
									prev: "' + textPrev + '",\
									next: "' + textNext + '",\
									duration: ' + String(content.settings.duration) + ' * 1000,\
									delay: ' + String(content.settings.delay) + ' * 1000,\
									width: ' + String(dimensions[0]) + ',\
									height: ' + String(dimensions[1]) + ',\
									autoPlay: ' + String(content.settings.autoPlay) + ',\
									stopOnHover: ' + String(content.settings.stopOnHover) + ',\
									loop: false,\
									bullets: true,\
									caption: ' + String(content.settings.caption) + ',\
									captionEffect: "' + String(content.settings.captionEffect) + '",\
									controls: ' + String(content.settings.controls) + ',\
									onBeforeStep: 0,\
									images: 0\
								});\
							</script>';

	var scriptvarSave = '<script id="wowslider-script_' + content.UUID + '">\
						var tester = ' + "'" + htmlvar + "'" + ';\
						$(tester).insertBefore($("#wowslider-script_' + content.UUID + '"));\
						jQuery("#wowslider-container_' + content.UUID + '").wowSlider({\
							effect: "' + String(content.settings.transistionEffect) + '",\
							prev: "' + textPrev + '",\
							next: "' + textNext + '",\
							duration: ' + String(content.settings.duration) + ' * 1000,\
							delay: ' + String(content.settings.delay) + ' * 1000,\
							width: ' + String(dimensions[0]) + ',\
							height: ' + String(dimensions[1]) + ',\
							autoPlay: ' + String(content.settings.autoPlay) + ',\
							stopOnHover: ' + String(content.settings.stopOnHover) + ',\
							loop: false,\
							bullets: true,\
							caption: ' + String(content.settings.caption) + ',\
							captionEffect: "' + String(content.settings.captionEffect) + '",\
							controls: ' + String(content.settings.controls) + ',\
							onBeforeStep: 0,\
							images: 0\
						});\
					</script>';

	if (typeof updateOld !== 'undefined' && updateOld)
	{
		if (ui.hasClass('WOWSliderRemoveBeforeSave'))
		{
			ui.parent().html(scriptvarCurrent + scriptvarSave.replace(/<script(?=(\s|>))/i, '<script type="text/xml" '));
		} else {
			ui.html(scriptvarCurrent + scriptvarSave.replace(/<script(?=(\s|>))/i, '<script type="text/xml" '));
		}
	}
	else {
		ui.draggable.remove();
	    GridController.convertPlaceholderToNormal();
	    GridController.getNewCell().append(scriptvarCurrent + scriptvarSave.replace(/<script(?=(\s|>))/i, '<script type="text/xml" '));
	    GridController.markNewCellAsOld();
	    
		$WOWSlider = makeWOWSliderDraggable($('<a />').addClass('WOWSliderDrag').data('WOWSlider', 1).css({ color: '#000000' }).html('<img src="img/icons/iconSlideshow.png" style="height: 20px; margin-right: 6px; margin-left: 5px;" /> <span style="font-size: 12px;">WaveSlider</span>'));
		$('div.widget-options-container > div.wowsliderContainer').html($WOWSlider);
		
	}

}
