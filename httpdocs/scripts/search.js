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

	if (searchTerm.length < 3) {
		var readyStateCheckInterval = setInterval(function() {
			if (document.readyState === "complete") {
				clearInterval(readyStateCheckInterval);
				document.getElementById("results").parentElement.innerHTML = "<br></br><h3 class='text-center'>Suchbegriff zu kurz</h3><br>";
			}
		}, 10);
		return;
	}

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
	var clans = result.clans;
	var member = result.member;
	if (clans.length > 0 ) {
		document.getElementById("results").innerHTML += "<h4 class='text-center'>Gefundene Clans</h4>";
		for (var i = 0; i < clans.length; i++) {
			var ClanName = clans[i].ClanName;
			document.getElementById("results").innerHTML += '<a style="padding-left: 40px" href="clanstats.html?clan=' + encodeURIComponent(ClanName) + '">' + ClanName + '</a><br>';
		}

		document.getElementById("results").innerHTML += "<hr>";
	}
	if (member.length > 0 ) {
		document.getElementById("results").innerHTML += "<h4 class='text-center'>Gefundene Spieler</h4>";
		for (var b = 0; b < member.length; b++) {
			document.getElementById("results").innerHTML += '<a style="padding-left: 40px" href="playerstats.html?player=' + encodeURIComponent(member[b].UUID) + '">' + member[b].name + '</a><br>';
		}
	}
}