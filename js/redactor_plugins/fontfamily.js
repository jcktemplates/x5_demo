/**
 * fontfamily
 * 
 * Original from http://imperavi.com/redactor/docs/plugins/ but heavily modified
 * by KJI/AFI
 * 
 */

if (!RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.fontfamily = {
	init: function ()
	{
        var fonts = {
			arial: ["Arial", "Arial, Helvetica, sans-serif"],
			arialblack: ["Arial Black", '"Arial Black", Gadget, sans-serif'],
			courier: ["Courier New", '"Courier New", Courier, monospace'],
			comicsans: ["Comic Sans", '"Comic Sans MS", cursive, sans-serif'],
			impact: ["Impact", 'Impact, Charcoal, sans-serif'],
			lucida: ["Lucida", '"Lucida Sans Unicode", "Lucida Grande", sans-serif'],
			lucidaconsole: ["Lucida Console", '"Lucida Console", Monaco, monospace'],
			georgia: ["Georgia", "Georgia, serif"],
			palatino: ["Palatino Linotype", '"Palatino Linotype", "Book Antiqua", Palatino, serif'],
			tahoma: ["Tahoma", "Tahoma, Geneva, sans-serif"],
			times: ["Times New Roman", "Times, serif"],
			trebuchet: ["Trebuchet", '"Trebuchet MS", Helvetica, sans-serif'],
			verdana: ["Verdana", "Verdana, Geneva, sans-serif"] 
		};
		var that = this;
		var dropdown = {};

		$.each(fonts, function(i, s)
		{
			dropdown['s' + i] = { title: "<span style='font-family: "+s[1]+"'>"+s[0]+"</span>", callback: function() { that.setFontfamily(s[1]); }};
		});

		this.buttonAddAfterNextSeparator('formatting', 'fontfamily', 'Change font family', false, dropdown);
	},
	setFontfamily: function (value)
	{
        this.inlineRemoveStyleRespectBordersOfElems('font-family', '', /\b(ff)\d+\b/gi);
		//this.exec('fontname', value);
        this.inlineSetStyle('font-family', value);
	}
};