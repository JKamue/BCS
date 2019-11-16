getPlayerRanking();
playerRanking = "";

const colorClass =["text-primary","text-success","text-success","text-success","text-warning","text-warning","text-warning","text-danger","text-danger","text-danger"];

function getPlayerRanking() {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET","api/api.php?getRanking", true);
    xmlhttp.send();

    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4) {
            console.log(JSON.parse(this.responseText));
            playerRanking = JSON.parse(this.responseText);
            // Wait for document to load
            var readyStateCheckInterval = setInterval(function() {
                if (document.readyState === "complete") {
                    clearInterval(readyStateCheckInterval);
                    showRanking();
                }
            }, 10);
        }
    };
}

function showRanking() {
    let selected = document.getElementById("rankSelector").value;

    let info = playerRanking[selected];

    var table = document.getElementById("rankingTable");
    table.innerHTML = "";

    for (let i = 0; i < 10; i++) {
        let player = info[i];
        var row = table.insertRow();
        var cell1 = row.insertCell(0);
        cell1.innerHTML = `<span class="${colorClass[i]}">${i+1}</span>`;
        var cell2 = row.insertCell(1);
        cell2.innerHTML= `<img style=\"width: 60px\" src=\"https://visage.surgeplay.com/face/128/${player.uuid}?overlay=true\">`;
        var cell3 = row.insertCell(2);
        cell3.innerHTML = `<a href=\"/playerstats.html?player=${player.uuid}\" class=\"text-with-space text-center\" style=\"color: #0000EE;\"><u>${player.name}</u><span>`;
    }
}