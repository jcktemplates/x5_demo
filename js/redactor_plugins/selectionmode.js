/**
 * selectionmode
 * 
 * Determine whether rangy should be used or not
 * 
 */

if (!RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.selectionmode = {
	init: function ()
	{
        if (this.oldIE()) this.opts.rangy = true;
	}
};