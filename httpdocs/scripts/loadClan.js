var gommeStats;
var bcsStats;
var relevantEnemies = [];

var clanname = "CowBuilders";

if (findGetParameter("clan") !== null) {
	clanname = findGetParameter("clan");
}
getBCSStats(clanname);

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
	xmlhttp.open("GET","https://jkamue.de/bcs/api/api.php?function=clanStats&name=" + name, true);
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
	var winlose = (gommeStats.wins / gommeStats.loses).toFixed(2);
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
	var rate = (bcsStats.clan[0].Wins / loses).toFixed(2);
	
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