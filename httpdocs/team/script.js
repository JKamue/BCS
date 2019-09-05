var tmp = "";
var supports = "";

function load() {
    showClans();
    showBCSInformation();
}

function httpGet(theUrl)
{
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
    xmlHttp.send( null );
    return JSON.parse(xmlHttp.responseText);
}

function formatTime(time) {
    var oneDay = 24*60*60*1000;
    var oneHour = 60*60*1000;

    var firstDate = new Date();
    var secondDate = new Date(time);

    var days = Math.round(Math.abs((firstDate.getTime() - secondDate.getTime())/(oneDay)));
    var hour = Math.round(Math.abs((firstDate.getTime() - secondDate.getTime())/(oneHour)));
    if (days > 1) {
        return days + " Tagen";
    } else {
        return hour + "h";
    }
}

function clanTable(item, index) {
    tmp += "<tr><td>" + item.ClanName + "</td>" +
        "<td>" + item.ClanTag + "</td><td>" + item.Games + "</td>" +
        "<td>" + item.DateAdded + "</td>" +
        "<td>" + formatTime(item.DateUpdated) + "</td>" +
        "<td>" + formatTime(item.LastActive) + "</td>" +
        "<td><a href='https://www.gommehd.net/clan-profile?name=" + encodeURIComponent(item.ClanName) + "' target='_blank'>Gomme</a> " +
        "<a href='https://www.gommehd.net/clan-match?id=" + encodeURIComponent(item.LastMatch) + "' target='_blank'>CW</a> " +
        "<a target='_blank' href='api.php?checkClan=" + encodeURIComponent(item.ClanName) + "'>Überprüfen</a> " +
        "<a target='_blank' href='api.php?checkActive=" + encodeURIComponent(item.ClanName) + "'>Aktiv prüfen</a></td></tr>";
}

function showClans() {
    var data = httpGet("api.php?showClans=1");

    document.getElementById("page-header").innerText = "BCS Clans";

    // Create the table
    tmp = "<table><tr><td>Name</td><td>Tag</td><td>Spielanzahl</td><td>Hinzugefügt</td><td>Aktualisiert vor</td><td>Aktiv vor</td><td>Aktion</td></tr>";
    data.forEach(clanTable);
    document.getElementById("page-content").innerHTML = tmp + "</table>";
}

function showAddClan() {
    document.getElementById("page-header").innerText = "Neuen Clan hinzufügen";
    document.getElementById("page-content").innerHTML = "<h3>Bitte beachte dass das anlegen eines neuen Clans bis zu 10min dauern kann.<br>" +
        "Wenn der Prozess abgeschlossen ist, wird hier eine Meldung erscheinen!<br>" +
        "Achte außerdem auf Groß- und Kleinschreibung im Clannamen.<br>" +
        "Bitte benutze dieses Browser Fenster während der Anlegung nicht.<a href='index.php' target='_blank'>Neues Öffnen</a> </h3>" +
        "<input type='text' id='clanname'><button onclick='addClan()'>Hinzufügen</button>";
}

function addClan() {
    var clan = document.getElementById("clanname").value;

    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", "api.php?addClan="+encodeURIComponent(clan), false ); // false for synchronous request
    xmlHttp.send( null );
    if (xmlHttp.status != 201) {
        document.getElementById("page-content").innerHTML = "<h1 style='color: red;'>" + xmlHttp.responseText + "<h1";
    } else {
        document.getElementById("page-content").innerHTML = "<h1 style='color: green;'>" + xmlHttp.responseText + "<h1";
    }
    showBCSInformation();
}

function showBCSInformation() {
    var dat = httpGet("api.php?BCSInformation=1");

    document.getElementById("clan-count").innerText = dat.Clans;
    document.getElementById("player-count").innerText = dat.Player;
    document.getElementById("cw-count").innerText = dat.Games;
}

function supportTable(item, index) {
    if (item.Magnitude == 3) {
        var color = "red";
    } else if (item.Magnitude == 2) {
        var color = "orange";
    } else {
        var color = "yellow";
    }
    tmp += "<tr><td style='background-color: " + color + "'></td><td>" + item.Header + "</td><td><a style='text-decoration: underline; cursor: pointer' onclick='support(\"" + index + "\")'>Supporten</a></td></tr>";
}

function showSupports() {
    supports = httpGet("api.php?getSupports=1");

    document.getElementById("page-header").innerText = "Supportfälle";

    tmp = "<table><tr><td>Dringlichkeit</td><td>Beschreibung</td></tr>";
    supports.forEach(supportTable);
    document.getElementById("page-content").innerHTML = tmp + "</table>";
}

function support(id) {
    var item = supports[id];

    document.getElementById("page-header").innerText = "Supportfall: " + item.ID + " - " + item.Header;
    document.getElementById("page-content").innerHTML = item.Description + "<br><br>" + item.Kontakt + "<hr><br>";
    document.getElementById("page-content").innerHTML += "Dringlichkeit setzen: <input type='number' max='3' min='1' maxlength='1' id='magnitude'><button onclick='setMagnitude(\"" + item.ID + "\")'>Setzen</button> 1-3, 3 ist das wichtigste<br>";
    document.getElementById("page-content").innerHTML += "Dieses Ticket als beendet markieren: <button onclick='closeSupport(\"" + item.ID + "\")'>Ticket schließen</button>";
}

function closeSupport(id) {
    httpGet("api.php?setDone=" + id);
    showSupports();
}

function setMagnitude(id) {
    var mag = document.getElementById("magnitude").value;
    httpGet("api.php?changeStatus=" + id + "&magnitude=" + mag);
    showSupports();
}

function teamTable(item, index) {
    if (item.rank == 1) {
        if (item.name == "Chimchu") {
            var color = "blue";
            var rank = "Developer";
        } else {
            var color = "red";
            var rank = "Admin";
        }
    } else if (item.rank == 2) {
        var color = "orange";
        var rank = "Mod";
    } else {
        var color = "green";
        var rank = "Supporter";
    }
    tmp += "<tr><td>" + item.name + "</td><td style='color:" + color + "'>" + rank + "</td><td>" +
        "<span style='cursor: pointer' onclick='setRank(1, \"" + item.name + "\")'>Admin</span> " +
        "<span style='cursor: pointer' onclick='setRank(2, \"" + item.name + "\")'>Moderator</span> " +
        "<span style='cursor: pointer' onclick='setRank(3, \"" + item.name + "\")'>Supporter</span> " +
        "<span style='cursor: pointer' onclick='deleteTeam(\"" + item.name + "\")'>Löschen</span></td></tr>";
    document.getElementById("page-content").innerHTML = tmp + "</table><br>Name: <input type='text' id='name'><button onclick='newTeam()'>Teammitglied erstellen</button>";
}

function showTeam() {
    document.getElementById("page-header").innerText = "Team";
    member = httpGet("api.php?team=1");
    tmp = "<table><tr><td>Name</td><td>Rang</td><td>Aktionen</td></tr>";
    member.forEach(teamTable);
}

function deleteClan() {
    var clan = prompt("Welchen Clan Möchtest du löschen?", "Cancel oder ESC zum Abbrechen drücken");
    if (confirm('Bist du dir sicher, dass du ' + clan + ' löschen willst?')) {
        document.getElementById("page-header").innerText = httpGet("api.php?delClan=" + encodeURIComponent(clan));
        document.getElementById("page-content").innerHTML = "";
    }
}

function newTeam() {
    var name = document.getElementById("name").value;
    alert(httpGet("api.php?addTeam=" + name));
    showTeam();
}

function setRank(rank, name) {
    httpGet("api.php?setRank=" + rank + "&name=" + name);
    showTeam();
}

function deleteTeam(name) {
    httpGet("api.php?deleteTeam=" + name);
    showTeam();
}