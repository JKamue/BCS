<?php
require_once "login.php";
$rank = $_SESSION['rank'];
$ranks = array("","Admin","Mod","Supporter");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BCS Teambereich</title>

    <script src="script.js"></script>
    <link type="text/css" rel="stylesheet" href="style.css">
</head>
<body onload="load()">

<div id="header">
    <h1>BCS Teambereich</h1>
</div>

<div style="overflow:auto">
    <div class="menu">
        <span onclick="showClans()">BCS-Clans</span>
        <span onclick="showAddClan()">Neuen Clan hinzufügen</span>
        <span onclick="showSupports()">Support</span>
        <?= ($rank == 1 ? "<span onclick=\"deleteClan()\">Clan löschen</span>" : ""); ?>
        <?= ($rank < 3 ? "<span onclick=\"showTeam()\">Team</span>" : ""); ?>
    </div>

    <div class="main">
        <h2 id="page-header">Verbindung zum Server wird aufgebaut</h2>
        <p id="page-content">Bitte kontaktiere Chimchu falls keine Verbindung aufgebaut wird</p>
    </div>

    <div class="right">
        <h2><?=$_SESSION['name']?> - <?=$ranks[$rank]?></h2>
        <p>
            Passwort ändern<br>
            Log out
        </p>
        <hr>
        <h2>BCS Stats: </h2>
        <p>
            Derzeitige Clans: <span id="clan-count"></span><br>
            Derzeitige Spieler: <span id="player-count"></span><br>
            Derzeitige CWs: <span id="cw-count"></span>
        </p>
    </div>
</div>


</body>
</html>

