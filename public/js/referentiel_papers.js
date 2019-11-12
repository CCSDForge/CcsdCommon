/**
 * 
 */

function load_document ( e, id, url ) {
	
	$("#" + id).load(url, function () {
		$(e).remove();
		
	});
	$("#" + id).show();
	
	
}