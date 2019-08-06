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

function addClan(String $name) : Array {
    ini_set('max_execution_time', 600);
    $return = array();
    $return['code'] = 201;
    $return['mes'] = "$name was added successfully";

    $pdo = Database::getConnection("main");

    // test if Clan exists on Gommehd.net
    $data = GommeApi::fetchClanStats($name);
    if ($data == false) {
        $return['code'] = 422;
        $return['mes'] = "$name does not exist";
        return $return;
    }

    // test if Clan exists in Database
    if (Clan::exists($data['uuid'],$pdo)) {
        $return['code'] = 409;
        $return['mes'] = "$name is already in BCS";
        return $return;
    }

    // Add the clan to BCS
    $time = date("Y-m-d H:i:s");
    $statement = $pdo->prepare("	INSERT IGNORE INTO clan (ClanUUID, ClanTag, ClanName, DateAdded, DateUpdated, LastActive, LastMatch)
	VALUES (?, ?, ?, ?, ?, ?, ?);");

    try {
        $statement->execute(array($data['uuid'], $data['tag'], $data['name'], $time, $time, "0000-00-00 00:00:00", 0));
    } catch (Exception $e) {
        $return['code'] = 500;
        $return['mes'] = "Failed to create clan";
        return $return;
        // TODO add to logger
    }

    // add each player to BCS
    $members = Clan::getMemberUUIDs($name);

    $player = $pdo->prepare("INSERT IGNORE INTO player (UUID, name)
	    VALUES (?, ?);");

    $update = $pdo->prepare("UPDATE player SET name = ? WHERE UUID = ?");

    $memberdb = $pdo->prepare("Insert IGNORE INTO member (PlayerID, Active, MVP, Betten, Kills, Killed, Quits, Died, BAC, ClanUUID, UUID)
	    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($members as &$member) {
        $player->execute(array($member['id'], $member['name']));
        $update->execute(array($member['name'], $member['id']));
        $memberdb->execute(array(md5($data['uuid'] . $member['id']), 1, 0, 0, 0, 0, 0, 0, 0, $data['uuid'], $member['id']));
    }

    // Add last uuid
    file_put_contents(ROOT . "/data/tmp/lastcw/" . $data['uuid'] . ".txt", "0+0000-00-00 00:00:00");

    // Scan the last x CWs
    $cws = GommeApi::fetchClanCws($data['uuid'],100);


    usort($cws, function ($a, $b) {
        $x = new DateTime($a['datetime']);
        $y = new DateTime($b['datetime']);
        return $x->format('Y-m-d H:i:s') <=> $y->format('Y-m-d H:i:s');
    });

    foreach ($cws as &$cw) {
        echo "cw ". $cw['matchid'];
        Clan::addCw($cw, $pdo, "bulk", $data);
        echo " added!<br>\n";
    }

    Clan::setActiveMembers($data, $pdo);
    return $return;

}
