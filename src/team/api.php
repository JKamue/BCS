<?php

function deleteTeam($name) {
    $sql = "DELETE FROM team WHERE name = ?";
    Database::execute($sql, array($name));
    return("true");
}

function setRank($rank, $name) {
    $sql = "UPDATE team SET rank = ? WHERE name = ?";
    Database::execute($sql, array($rank, $name));
    return("true");
}

function newTeam($name) {
    $pass = mt_rand(100000, 999999);
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "INSERT INTO team (name, password, rank) VALUES (?, ?, ?)";
    Database::execute($sql, array($name, $hash, 3));

    return $pass;
}

function delClan($name) {
    // TODO LOGGING
    $sql = "DELETE FROM clan WHERE ClanName = ?";
    try {
        Database::execute($sql, array($name));
        return "Clan wurde gelöscht";
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function getTeam() {
    $sql = "SELECT name, rank FROM team";
    return Database::select($sql, array());
}

function listClans() {
    $sql = "SELECT clan.ClanUUID, clan.ClanName, clan.ClanTag, clan.DateAdded, clan.DateUpdated, clan.LastActive, clan.LastMatch, COUNT(game.GameID) as Games FROM `clan` INNER JOIN `game` ON clan.ClanUUID = game.ClanUUID GROUP BY clan.ClanName";
    return Database::select($sql, array());
}

function changeStatus($id,$mag) {
    $sql = "UPDATE support SET Magnitude = ? WHERE ID = ?";
    Database::execute($sql, array($mag,$id));
}

function setDone($id) {
    $sql = "UPDATE support SET Status = 0 WHERE ID = ?";
    Database::execute($sql, array($id));
}

function getStats() {
    $sql = "SELECT  (
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
    FROM    dual";
    return Database::selectFirst($sql, array());
}

function getSupport() {
    return Database::select("SELECT * FROM `support` WHERE Status = 1", array());
}
