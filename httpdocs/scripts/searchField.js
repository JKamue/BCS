function searchForClans() {
	var val = document.getElementById("searchField").value;
	
	if ( val != "" && val != null) {
		window.location = "search.html?search=" + val;
	}
	
	var val2 = document.getElementById("searchField2").value;
	
	if ( val2 != "" && val2 != null) {
		window.location = "search.html?search=" + val;
	}
}