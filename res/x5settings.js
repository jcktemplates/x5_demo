(function ( $, x5engine ) {
	var s = x5engine.settings,
		b = x5engine.boot;

	b.push(function () {
		x5engine.setupDateTime();
		x5engine.imAccess.showLogout();
		x5engine.utils.autoHeight();
		x5engine.cart.ui.updateWidget();
		x5engine.imGrid.init();
	});
	b.push(function () {
		x5engine.menu({
			target: '#imMnMn',
			showCurrent: false,
			verticalScroll: false,
			orientation: 'horizontal',
			menuHeight: 64,
			menuWidth: 135,
			submenuHeight: 22,
			submenuWidth: 160,
			opacity: 0.800,
			type: 'singleColumn',
			alignment: 'left',
			effect: 'fade'
		});
	});

	// ShowBox
	$.extend(s.imShowBox, {
		'effect' : 'fade',
		'shadow' : '3px 3px 10px 0px #808080',
		'background' : '#000000',
		'borderWidth' : {
			'top': 1,
			'right': 1,
			'bottom': 1,
			'left': 1
		},
		'borderRadius' : '5px 5px 5px 5px',
		'borderColor' : '#000000 #000000 #000000 #000000',
		'textColor' : '#000000',
		'fontFamily' : 'Tahoma',
		'fontStyle' : 'normal',
		'fontWeight' : 'normal',
		'fontSize' : '9pt',
		'textAlignment' : 'left',
		'boxColor' : '#FFFFFF',
		'opacity' : 0.700,
		'radialBg' : true // Works only in Mozilla Firefox and Google Chrome
	});

	// PopUp
	$.extend(s.imPopUp, {
		'effect' : 'fade',
		'width' : 500,
		'shadow' : '3px 3px 10px 0px #808080',
		'background' : '#000000',
		'borderRadius' : 10,
		'textColor' : '#000000',
		'boxColor' : '#FFFFFF',
		'opacity' : 0.700
	});

	// Tip
	$.extend(s.imTip, {
		'borderRadius' : 5,
		'arrow' : false,
		'position' : 'bottom',
		'effect' : 'bounce',
		'showTail' : false
	});

	// Captcha
	var codes = s.imCaptcha.offlineCodes;
	s.loaded = true;
})( _jq, x5engine );