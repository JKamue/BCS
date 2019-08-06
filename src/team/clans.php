<?php

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
    if (ClanOld::exists($data['uuid'],$pdo)) {
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
    $members = ClanOld::getMemberUUIDs($name);

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
        ClanOld::addCw($cw, $pdo, "bulk", $data);
        echo " added!<br>\n";
    }

    ClanOld::setActiveMembers($data, $pdo);
    return $return;

}
