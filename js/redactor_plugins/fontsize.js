/**
 * fontsize
 * 
 * Original from http://imperavi.com/redactor/docs/plugins/ but modified
 * by KJI
 * 
 */

if (!RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.fontsize = {
	init: function()
	{
		var fonts = [10, 11, 12, 14, 16, 18, 20, 24, 28, 30];
		var that = this;
		var dropdown = {};

		$.each(fonts, function(i, s)
		{
			//dropdown['s' + i] = { title: s + 'px', callback: function() { that.setFontsize(s); } };
			dropdown['s' + i] = { title: '<span style="font-size: '+s+'px;">' + s + '</span>', callback: function() { that.setFontsize(s); } };
		});

		dropdown['remove'] = { title: 'Remove font size', callback: function() { that.resetFontsize(); } };

		this.buttonAddAfter('fontfamily', 'fontsize', 'Change font size', false, dropdown);
	},
	setFontsize: function(size)
	{
		//this.inlineRemoveStyleRespectBordersOfElems('font-size', size + 'px');
		this.inlineSetStyle('font-size', size + 'px');
	},
	resetFontsize: function()
	{
		this.inlineRemoveStyleRespectBordersOfElems('font-size', '', /\b(fs)\d+\b/gi);
	}
};