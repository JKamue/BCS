function searchForClans(id) {
	var val = document.getElementById(id).value;
	
	if ( val != "" && val != null) {
		window.location = "search.html?search=" + val;
	}
}

function checkIfEnter(e, id) {
	if (e.keyCode == 13) {
        searchForClans(id);
    }
}