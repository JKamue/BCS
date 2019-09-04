function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
          tmp = item.split("=");
          if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
}

var searchTerm = escape(findGetParameter("search"));
search(searchTerm);
document.getElementById("result").innerText = searchTerm;


function search(searchTerm) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET","api/api.php?search=" + searchTerm, true);
	xmlhttp.send();
		
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4) {
			result = JSON.parse(this.responseText);
			loadResult(result);
		}
		
	}
}

function loadResult(result) {
	document.getElementById("results").innerHTML = "";
	for (var i = 0; i < result.length; i++) {
		var ClanName = result[i].ClanName;
		document.getElementById("results").innerHTML += '<a style="padding-left: 40px" href="clanstats.html?clan=' + encodeURIComponent(ClanName) + '">' + ClanName + '</a><br>';
	}
}