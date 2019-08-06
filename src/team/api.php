<?php


function deleteTeam($name) {
    $pdo = Database::getConnection("main");
    $clan = $pdo->prepare("DELETE FROM team WHERE name = ?");
    $clan->execute(array($name));
    return("true");
}

function setRank($rank, $name) {
    $pdo = Database::getConnection("main");
    $clan = $pdo->prepare("UPDATE team SET rank = ? WHERE name = ?");
    $clan->execute(array($rank, $name));
    return("true");
}

function newTeam($name) {
    $pass = mt_rand(100000, 999999);
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $pdo = Database::getConnection("main");
    $clan = $pdo->prepare("INSERT INTO team (name, password, rank) VALUES (?, ?, ?)");
    $clan->execute(array($name, $hash, 3));

    return $pass;
}

function delClan($name) {
    // TODO LOGGING
    $pdo = Database::getConnection("main");
    $clan = $pdo->prepare("DELETE FROM clan WHERE ClanName = ?");
    try {
        $clan->execute(array($name))  or die(print_r($clan->errorInfo(), true));
        return "Clan wurde gelöscht";
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function getTeam() {
    $pdo = Database::getConnection("main");
    $team = $pdo->prepare("SELECT name, rank FROM team");
    $team->execute();
    $result = $team->fetchAll(\PDO::FETCH_ASSOC);
    return $result;
}

function listClans() {
    $pdo = Database::getConnection("main");
    $clans = $pdo->prepare("SELECT clan.ClanUUID, clan.ClanName, clan.ClanTag, clan.DateAdded, clan.DateUpdated, clan.LastActive, clan.LastMatch, COUNT(game.GameID) as Games FROM `clan` INNER JOIN `game` ON clan.ClanUUID = game.ClanUUID GROUP BY clan.ClanName");
    $clans->execute();
    $result = $clans->fetchAll(\PDO::FETCH_ASSOC);
    return $result;
}

function changeStatus($id,$mag) {
    $pdo = Database::getConnection("main");
    $status = $pdo->prepare("UPDATE support SET Magnitude = ? WHERE ID = ?");
    $status->execute(array($mag,$id));
}

function setDone($id) {
    $pdo = Database::getConnection("main");
    $status = $pdo->prepare("UPDATE support SET Status = 0 WHERE ID = ?");
    $status->execute(array($id));
}

function getStats() {
    $pdo = Database::getConnection("main");
    $stats = $pdo->prepare("SELECT  (
        SELECT COUNT(*)
        FROM   clan
        ) AS Clans,
        (
        SELECT COUNT(*)
        FROM   player
        ) AS Player,
        (
        SELECT COUNT(*)
        FROM   game
        ) AS Games
    FROM    dual");
    $stats->execute();
    $result = $stats->fetch();
    return $result;
}

function getSupport() {
    $pdo = Database::getConnection("main");
    $sup = $pdo->prepare("SELECT * FROM `support` WHERE Status = 1");
    $sup->execute();
    $res = $sup->fetchAll(\PDO::FETCH_ASSOC);
    return $res;
}
