function searchForClans(id) {
	var val = document.getElementById(id).value;
	
	if ( val != "" && val != null) {
		window.location = "search.html?search=" + val;
	}
}