var gommeStats;
var bcsStats;
var relevantEnemies = [];

var clanname = "CowBuilders";

if (findGetParameter("clan") !== null) {
	clanname = findGetParameter("clan");
}
getBCSStats(encodeURIComponent(clanname));
getGommeStats(encodeURIComponent(clanname));


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

function timeCalculator(seconds) {
	seconds = Math.floor(seconds);
	if (seconds < 60) {
		return seconds + " s";
	}
	
	var min = Math.floor(seconds / 60);
	if (min < 60) {
		return min + " min";
	}
	
	var hours = Math.floor(min / 60);
	if (hours < 24) {
		return hours + " h";
	}
	
	var days = Math.floor(hours / 24);
	if (days < 7) {
		return days + " Tagen";
	}
	
	var weeks = Math.floor(days / 7);
	if (days < 30) {
		return weeks + " Wochen";
	}
	
	var months = Math.floor(days / 30);
	return months +  " Mon";
}

function loadId(id) {
	return document.getElementById(id);
}

function setText(id, text) {
	loadId(id).innerText = text;
}

function getGommeStats(name) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET","api/gommeapi/api.php?function=clanStats&name=" + name, true);
	xmlhttp.send();
		
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4) {
			gommeStats = JSON.parse(this.responseText);
			// Wait for document to load
			var readyStateCheckInterval = setInterval(function() {
				if (document.readyState === "complete") {
					clearInterval(readyStateCheckInterval);
					loadGommeStats();
				}
			}, 10);
		}
	};
}

function loadGommeStats() {
	var games = 1 * gommeStats.wins + 1 * gommeStats.loses;
	var winlose = Math.round(gommeStats.wins / games * 100);
	setText("data-gomme-games", games);
	setText("data-gomme-rank", gommeStats.rank);
	setText("data-gomme-elo", gommeStats.elo);
	setText("data-gomme-wins", gommeStats.wins);
	setText("data-gomme-loses", gommeStats.loses);
	setText("data-gomme-winrate", winlose);
	
}

function getBCSStats(name) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET","api/api.php?clanname=" + name, true);
	xmlhttp.send();
		
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4) {
			bcsStats = JSON.parse(this.responseText);
			// Wait for document to load
			var readyStateCheckInterval = setInterval(function() {
				if (document.readyState === "complete") {
					clearInterval(readyStateCheckInterval);
					if (bcsStats.mes === "Clan not in BCS") {
						getGommeStats("CowBuilders");
						getBCSStats("CowBuilders");
					} else {
						loadBcsStats();
					}
				}
			}, 10);
		}
	};
}

function loadBcsStats() {
	// Header
	setText("data-clanname", bcsStats.clan[0].Name);
	setText("data-clantag", bcsStats.clan[0].tag);
	
	// Clan Stats
	var loses = bcsStats.clan[0].games - bcsStats.clan[0].Wins;
	var rate = Math.round(bcsStats.clan[0].Wins / bcsStats.clan[0].games * 100) ;
	
	var now = new Date();
	var active = new Date(bcsStats.clan[0].active);
	var updated = new Date(bcsStats.clan[0].updated);
	var timeDiffActive = Math.abs(now.getTime() - active.getTime()) / 1000;
	var timeDiffUpdated = Math.abs(now.getTime() - updated.getTime()) / 1000;
	
	var active = bcsStats.clan[0].added.split(" ");
	var dates = active[0].split("-");
	
	
	setText("data-bcs-games",bcsStats.clan[0].games);
	setText("data-bac-games",bcsStats.clan[0].Bac);
	setText("data-bcs-dm",bcsStats.clan[0].dms);
	setText("data-bcs-wins",bcsStats.clan[0].Wins);
	setText("data-bcs-loses",bcsStats.clan[0].games - bcsStats.clan[0].Wins);
	setText("data-bcs-winrate",rate);
	setText("data-bcs-playtime",bcsStats.clan[0].time);
	setText("data-bcs-active",timeCalculator(timeDiffActive));
	setText("data-bcs-updated",timeCalculator(timeDiffUpdated));
	setText("data-bcs-added",dates[2] + "." + dates[1] + "." + dates[0]);
	
	// Enemy Stats
	getRelevantEnemies();
	enemySetFilter("enemy-best");
	
	// Map Stats
	mapSetSorter("map-best")
	
	// Lineup Stats
	lineupSetFilter("lineup-games");
	
	// Player Stats
	playerSetSorter("player-games");
}


function playerSetSorter(sorter) {
	playerFocusSorter(sorter);
	playerSort(sorter);
	loadPlayers();
}

function playerFocusSorter(sorter) {
	loadId("player-games").classList.remove("underline");
	loadId("player-winlose").classList.remove("underline");
	loadId("player-beds").classList.remove("underline");
	loadId("player-mvp").classList.remove("underline");
	loadId("player-kd").classList.remove("underline");
	loadId(sorter).classList.add("underline");
}

function playerSort(sorter) {
	if (sorter == "player-winlose") {
		playerSort("most");
		bcsStats.member = bcsStats.member.sort(function(a, b) {
			var arate = getRate(a.games, a.wins);
			var brate = getRate(b.games, b.wins);
			return brate - arate
		});
	} else if (sorter == "player-beds") {
		playerSort("most");
		bcsStats.member = bcsStats.member.sort(function(a, b) {
			return b.beds - a.beds;
			return winDiff;
		});
	} else if (sorter == "player-mvp") {
		playerSort("most");
		bcsStats.member = bcsStats.member.sort(function(a, b) {
			return b.mvp - a.mvp;
			return winDiff;
		});
	} else if (sorter == "player-kd") {
		playerSort("most");
		bcsStats.member = bcsStats.member.sort(function(a, b) {
			var akd = a.kills / (1*a.quits + 1*a.died + 1*a.killed);
			var bkd = b.kills / (1*b.quits + 1*b.died + 1*b.killed);
			return bkd - akd;
		});
	} else {
		bcsStats.member = bcsStats.member.sort(function(a, b) {
			return b.games - a.games;
		});
	}
}

function loadPlayers() {
	document.getElementById("member-content").scrollTop = 0;
	var table = loadId("data-member");
	table.innerHTML = "";
	for (var i = 0; i < bcsStats.member.length; i++) {
		var member = bcsStats.member[i];
		
		var row = table.insertRow();
		var cell1 = row.insertCell(0);
		var cell2 = row.insertCell(1);
		
		var loses = member.games - member.wins;
		var winlose = Math.round((member.wins / member.games) * 100);
		var kd = (member.kills / (1* member.quits + 1*  member.died + 1* member.killed)).toFixed(2);
		if (kd == Infinity) {
			kd = "&infin;";
		}
		
		cell1.innerHTML = '<img style="width: 60px" src="https://crafatar.com/avatars/' + member.uuid +'?overlay=true"/>';
		cell1.style.verticalAlign = "middle";
		
		cell2.innerHTML = "<h5>\n" +
							"\t\t\t\t\t\t\t<span class=\"text-with-space text-success\">" + member.wins + "<span><wbr>\n" +
							"\t\t\t\t\t\t\t<span class=\"text-with-space text-danger\">" + loses + "<span><wbr>\n" +
							"\t\t\t\t\t\t\t<span class=\"text-with-space text-bcs\">KD " + kd + "<span><wbr>\n" +
							"\t\t\t\t\t\t\t<span class=\"text-with-space text-muted\"><i class=\"fas fa-running\"></i> " + member.quits + "<span><wbr>\n" +
							"\t\t\t\t\t\t\t<span class=\"text-with-space text-secondary\">" + winlose + "%<span><wbr>\n" +
							"\t\t\t\t\t\t\t<span class=\"text-with-space text-bcs\"><i class=\"fas fa-bed\"></i> " + member.beds + "<span><wbr>\n" +
							"\t\t\t\t\t\t\t<span class=\"text-with-space text-bcs\">MVP " + member.mvp + "<span><wbr>\n" +
							"\t\t\t\t\t\t<span class=\"text-with-space text-secondary text-center\">" + member.name + "<span><wbr>\n</h5>";
	}
}



function lineupSetFilter(filter) {
	lineupFocusFilter(filter);
	lineupSort(filter);
	loadLineups();
}

function lineupFocusFilter(filter) {
	loadId("lineup-games").classList.remove("underline");
	loadId("lineup-best").classList.remove("underline");
	loadId("lineup-worst").classList.remove("underline");
	loadId(filter).classList.add("underline");
}

function lineupSort(filter) {
	if (filter == "lineup-best") {
		lineupSort("most");
		bcsStats.lineupstats = bcsStats.lineupstats.sort(function(a, b) {
			var winDiff = getRate(b.games, b.wins) - getRate(a.games, a.wins);
			return winDiff;
		});
	} else if (filter == "lineup-worst") {
		lineupSort("most");
		bcsStats.lineupstats = bcsStats.lineupstats.sort(function(a, b) {
			var looseDiff = getRate(a.games, a.wins) - getRate(b.games, b.wins);
			return looseDiff;
		});
	} else if (filter == "lineup-time") {
		bcsStats.lineupstats = bcsStats.lineupstats.sort(function(a, b) {
			return b.time - a.time;
		});
	} else {
		lineupSort("lineup-time");
		bcsStats.lineupstats = bcsStats.lineupstats.sort(function(a, b) {
			return b.games - a.games;
		});
	}
}

function loadLineups() {
	document.getElementById("data-lineups").scrollTop = 0;
	var container = loadId("data-lineups");
	container.innerHTML = "<br>";
	for (var i = 0; i < bcsStats.lineupstats.length; i++) {
		if (i > 19) {
			break;
		}
		
		var lineup = bcsStats.lineupstats[i];
		var loses = lineup.games - lineup.wins;
		var rate = Math.round((lineup.wins / lineup.games) * 100);
		var time = (lineup.time / 60).toFixed(1);
		
		container.innerHTML +="\t\t\t<div id=\"" + lineup.LineupID + "\" class=\"row w-105 text-center\">\n" +
						"\t\t\t\t<div class=\"col-md-12 col-lg-6 col-xl-5 stats-box\">\n" +
						"\t\t\t\t\t<img style=\"width: 60px\" src=\"https://crafatar.com/avatars/" + lineup.Player1UUID + "?overlay=true\"/>\n" +
						"\t\t\t\t\t<img style=\"width: 60px\" src=\"https://crafatar.com/avatars/" + lineup.Player2UUID + "?overlay=true\"/>\n" +
						"\t\t\t\t\t<img style=\"width: 60px\" src=\"https://crafatar.com/avatars/" + lineup.Player3UUID + "?overlay=true\"/>\n" +
						"\t\t\t\t\t<img style=\"width: 60px\" src=\"https://crafatar.com/avatars/" + lineup.Player4UUID + "?overlay=true\"/>\n" +
						"\t\t\t\t</div>\n" +
						"\t\t\t\t<div class=\"col-md-12 col-lg-6 col-xl-7 stats-box\" style=\"display: flex;justify-content: center;flex-direction: column;\">\n" +
						"\t\t\t\t\t<h5>\n" +
						"\t\t\t\t\t\t<span class=\"text-with-space text-success\">" + lineup.wins + "<span><wbr>\n" +
						"\t\t\t\t\t\t<span class=\"text-with-space text-danger\">" + loses + "<span><wbr>\n" +
						"\t\t\t\t\t\t<span class=\"text-with-space text-secondary\">" + rate + "%<span><wbr>\n" +
						"\t\t\t\t\t\t<span class=\"text-with-space text-bac\"><img height=\"25px\" src=\"img/badlion.png\"/> " + lineup.bac + "<span><wbr>\n" +
						"\t\t\t\t\t\t<span class=\"text-with-space text-secondary\"><i class=\"fas fa-stopwatch\"></i> " + time + "h<span><wbr>\n" +
						"\t\t\t\t\t\t<span class=\"text-with-space text-secondary\">DM: " + lineup.dms + "<span><wbr>\n" +
						"\t\t\t\t\t\t<span class=\"text-with-space text-info\">Elo: " + lineup.elo + "</span><wbr>\n" +
						"\t\t\t\t\t</h5>\n" +
						"\t\t\t\t</div>\n" +
						"\t\t\t</div>\n" +
						"\t\t\t<hr>\n";
	}
}



function mapSetSorter(sorter) {
	mapFocusSorter(sorter);
	mapSort(sorter);
	loadMaps();
}

function mapFocusSorter(sorter) {
	loadId("map-best").classList.remove("underline");
	loadId("map-worst").classList.remove("underline");
	loadId("map-most").classList.remove("underline");
	loadId(sorter).classList.add("underline");
}

function mapSort(sorter) {
	if (sorter == "map-best") {
		mapSort("most");
		bcsStats.maps = bcsStats.maps.sort(function(a, b) {
			var winDiff = getRate(b.games, b.wins) - getRate(a.games, a.wins);
			return winDiff;
		});
	} else if (sorter == "map-worst") {
		mapSort("most");
		bcsStats.maps = bcsStats.maps.sort(function(a, b) {
			var looseDiff = getRate(a.games, a.wins) - getRate(b.games, b.wins);
			return looseDiff;
		});
	} else {
		bcsStats.maps = bcsStats.maps.sort(function(a, b) {
			return parseFloat(b.games) - parseFloat(a.games);
		});
	}
}

function loadMaps() {
	document.getElementById("map-content").scrollTop = 0;
	var table = loadId("data-maps");
	table.innerHTML = "";
	for (var i = 0; i < bcsStats.maps.length; i++) {
		var map = bcsStats.maps[i];
		var loses = map.games - map.wins;
		var rate = Math.round((map.wins / map.games) * 100);
		
		var row = table.insertRow();
		var cell1 = row.insertCell(0);
		var cell2 = row.insertCell(1);
		var cell3 = row.insertCell(2);
		var cell4 = row.insertCell(3);
		var cell5 = row.insertCell(4);
		
		cell1.innerHTML = '<img class="map" src="img/maps/' + map.name.toLowerCase() + '.jpg"/>';
		cell2.innerHTML = '<h5 class="text-bcs hide-on-tiny">' + map.name + '</h5>';
		cell3.innerHTML = '<h5 class="text-success">' + map.wins + '</h5>';
		cell4.innerHTML = '<h5 class="text-danger">' + loses + '</h5>';
		cell5.innerHTML = '<h5 class="text-secondary">' + rate + '%</h5>';
	}
}




function enemySetFilter(filter) {
	enemyFocusFilter(filter);
	enemySortByFilter(filter);
	loadEnemyList();
}

function getRate(games, wins) {
	return wins / (games - wins);
}

function enemySortByFilter(filter) {
	if (filter == "enemy-best") {
		enemySortByFilter("most");
		relevantEnemies = relevantEnemies.sort(function(a, b) {
			var winDiff = getRate(b.games, b.wins) - getRate(a.games, a.wins);
			return winDiff;
		});
	} else if (filter == "enemy-worst") {
		enemySortByFilter("most");
		relevantEnemies = relevantEnemies.sort(function(a, b) {
			var looseDiff = getRate(a.games, a.wins) - getRate(b.games, b.wins);
			return looseDiff;
		});
	} else {
		relevantEnemies = relevantEnemies.sort(function(a, b) {
			return parseFloat(b.games) - parseFloat(a.games);
		});
	}
}

function loadEnemyList() {
	document.getElementById("enemy-content").scrollTop = 0;
	var table = loadId("data-enemies");
	table.innerHTML = "";
	for (var i = 0; i < relevantEnemies.length; i++) {
		if (i == 20) {
			break;
		}
		
		var enemy = relevantEnemies[i];
		var loses = enemy.games - enemy.wins;
		var rate = Math.round((enemy.wins / enemy.games) * 100);
		
		var row = table.insertRow();
		var cell1 = row.insertCell(0);
		var cell2 = row.insertCell(1);
		var cell3 = row.insertCell(2);
		var cell4 = row.insertCell(3);
		
		cell1.innerHTML = '<h5 class="text-bcs inline">' + enemy.name + ' <small class="hide-on-tiny">[' + enemy.tag + ']</small></h5>';
		cell2.innerHTML = '<h5 class="text-success">' + enemy.wins + '</h5>';
		cell3.innerHTML = '<h5 class="text-danger">' + loses + '</h5>';
		cell4.innerHTML = '<h5 class="text-secondary">' + rate + '%</h5>';
	}
}

function enemyFocusFilter(filter) {
	loadId("enemy-best").classList.remove("underline");
	loadId("enemy-worst").classList.remove("underline");
	loadId("enemy-most").classList.remove("underline");
	loadId(filter).classList.add("underline");
}

function getRelevantEnemies() {
	var enemies = bcsStats.enemy;
	for (var i = 0; i < enemies.length; i++) {
		var enemy = enemies[i];
		if (enemy.games > 1 && enemy.name != "DeletedClan") {
			relevantEnemies.push(enemy);
		}
	}
}