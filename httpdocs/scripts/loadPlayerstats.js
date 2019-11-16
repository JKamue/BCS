var playerStats;

var uuid = "CowBuilders";

if (findGetParameter("player") !== null) {
	uuid = findGetParameter("player");
}

getPlayerStats(uuid);


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

function loadId(id) {
	return document.getElementById(id);
}


function getPlayerStats(uuid) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET","api/api.php?member=" + uuid, true);
	xmlhttp.send();
		
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4) {
			console.log(JSON.parse(this.responseText));
			playerStats = JSON.parse(this.responseText);
			// Wait for document to load
			var readyStateCheckInterval = setInterval(function() {
				if (document.readyState === "complete") {
					clearInterval(readyStateCheckInterval);
					if (playerStats.clans[0] === undefined) {
						getPlayerStats("7da9fe86e2e046e3a9bb748f2df1c6b3");
					} else {
						loadPlayerStats();
					}
				}
			}, 10);
		}
	};
}

function loadPlayerStats() {
	loadId("data-playername").innerText = playerStats.clans[0].name;
	
	loadAnimation();
	loadPresent();
	loadRanking();
	loadPast();
}

function loadAnimation() {
	animatePlayer(playerStats.clans[0].name, "player-render")
}

function loadRanking() {
	if (playerStats.history !== undefined) {
		loadId("ranking").classList.add("text-center");
		if (activeClan == null) {
			loadId("ranking").innerText = "Kein BCS Clan";
		} else {
			loadId("ranking").innerText = "Noch nicht genug Spiele";
		}
	} else {
		loadId("ranking").innerHTML = "" +
		"Spiele: Platz " + playerStats.games + "<br>\n" +
		"\t\t\tSpiele mit BAC: Platz " + playerStats.bac + "<br>\n" +
		"\t\t\tMVP: Platz " + playerStats.mvp + "<br>\n" +
		"\t\t\tWinlose: Platz " + playerStats.winlose + "<br>\n" +
		"\t\t\tKD: Platz " + playerStats.kd + "<br>\n" +
		"\t\t\tBetten: Platz " + playerStats.bac + "<br>\n" +
		"\t\t\tSelbstmorde: Platz " + playerStats.suicide + "<br>\n" +
		"\t\t\tRagequits: Platz " + playerStats.quits + "<br>\n";
	}
}

function loadPast() {
	var amountFound = 0;
	for (var i = 0; i < playerStats.clans.length; i++) {
		var clan = playerStats.clans[i];
		if (clan.active != 1) {
			amountFound++;
			
			var winlose = Math.round(clan.wins / clan.games * 100);
		
			var kd = (clan.kills / (1* clan.quits + 1*  clan.died + 1* clan.killed)).toFixed(2);
			if (kd == Infinity) {
				kd = "&infin;";
			}
			
			var history = loadId("history");
			var clanBox = document.createElement('li');
			clanBox.innerHTML = "" + 
			"\t\t\t\t<a target=\"_blank\" href=\"clanstats.html?clan=" + encodeURIComponent(clan.clan) + "\">" + clan.clan + "</a>\n" +
			"\t\t\t\t\t<p>\n" +
			"\t\t\t\t\t\t<table class=\"w-100\">\n" +
			"\t\t\t\t\t\t\t<tr>\n" +
			"\t\t\t\t\t\t\t\t<td>Games: " + clan.games + "</td>\n" +
			"\t\t\t\t\t\t\t\t<td>Winlose: " + winlose + "%</td>\n" +
			"\t\t\t\t\t\t\t</tr>\n" +
			"\t\t\t\t\t\t\t<tr>\n" +
			"\t\t\t\t\t\t\t\t<td>Wins: " + clan.wins + "</td>\n" +
			"\t\t\t\t\t\t\t\t<td>Loses: " + (clan.games - clan.wins) + "</td>\n" +
			"\t\t\t\t\t\t\t</tr>\n" +
			"\t\t\t\t\t\t\t<tr>\n" +
			"\t\t\t\t\t\t\t\t<td>Bettem: " + clan.beds + "</td>\n" +
			"\t\t\t\t\t\t\t\t<td>MVP: " + clan.mvp + "</td>\n" +
			"\t\t\t\t\t\t\t</tr>\n" +
			"\t\t\t\t\t\t\t<tr>\n" +
			"\t\t\t\t\t\t\t\t<td>KD: " + kd + "</td>\n" +
			"\t\t\t\t\t\t\t\t<td>Ragequits: " + clan.quits + "</td>\n" +
			"\t\t\t\t\t\t\t</tr>\n" +
			"\t\t\t\t\t\t</table>\n" +
			"\t\t\t\t\t</p>"
			history.appendChild(clanBox);
		}
	}
	
	if (amountFound == 0) {
		loadId("history").outerHTML = "<h3 class='text-danger text-center'>Keine anderen BCS Clans</h3>";
		loadId("history-box").classList.add("hide-on-tiny");
	}
}

function loadPresent() {
	activeClan = null;
	for (var i = 0; i < playerStats.clans.length; i++) {
		var clan = playerStats.clans[i];
		if (clan.active == 1) {
			activeClan = clan;
		}
	}
	
	if (activeClan == null) {
		loadId("active-stats-box").outerHTML = "";
		loadId("active-clan").innerHTML = "Kein BCS Clan"
	} else {
		var winlose = Math.round(activeClan.wins / activeClan.games * 100);
		
		var kd = (activeClan.kills / (1* activeClan.quits + 1*  activeClan.died + 1* activeClan.killed)).toFixed(2);
		if (kd == Infinity) {
			kd = "&infin;";
		}
		
		loadId("active-clan").innerHTML = "<a href='clanstats.html?clan=" + activeClan.clan + "'>" + activeClan.clan + " <small>" + activeClan.tag + "</small></a>";
		
		loadId("active-games").innerText = "Games: " + activeClan.games;
		loadId("active-winlose").innerText = "Winlose: " + winlose + "%";
		loadId("active-wins").innerText = "Wins: " + activeClan.wins;
		loadId("active-loses").innerText = "Loses: " + (activeClan.games - activeClan.wins);
		loadId("active-beds").innerText = "Betten: " + activeClan.beds;
		loadId("active-mvp").innerText = "MVP: " + activeClan.mvp;
		loadId("active-kd").innerHTML = "KD: " + kd;
		loadId("active-quits").innerText = "Ragequits: " + activeClan.quits;
	}
}

