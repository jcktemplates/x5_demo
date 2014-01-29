function queryBackend(URL, data, onSuccess, type) {
	$.ajax({
		url: 		URL,
		dataType:	'json',
		type: 		type,
		async: 		true,
		cache: 		false,
		timeout: 	30000,
		data: 		data,
		error: 		function(request, status, error){
			//console.log(request.responseText);
		},
		success: 	function(response){
			onSuccess(response);
		}
	});	
}