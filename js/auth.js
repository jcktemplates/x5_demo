$(document).ready(function() {
	$('#iconLoginKey').bind('mouseover', function() {
		$(this).css({ opacity: 1.0 });
	}).bind('mouseout', function() {
		$(this).css({ opacity: 0.2 });
	});
});