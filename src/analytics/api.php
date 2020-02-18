<?php

function getAllClansAndNumbers()
{
    $return = [];
    $sql = "SELECT ClanName FROM `clan`";
    $data = Database::select($sql,[]);

    for ($i = 0; $i < count($data); $i++) {
        $return[$data[$i]["ClanName"]] = $i;
    }

    return $return;
}

function getAllMemberHistories(int $i)
{
    $sql = "SELECT clan.ClanName, player.name FROM `member`
	JOIN clan ON clan.ClanUUID = member.ClanUUID
	JOIN player ON player.UUID = member.UUID";

    $return = [];
    $return["reference"] = array();
    $data = Database::select($sql,[]);

    foreach ($data as &$player) {
        array_push($return["reference"], array($player["name"], $player["ClanName"]));
        $return["nodes"][$i] = $player["name"];
        $i++;
    }

    return $return;
}

function createNodesAndEdges()
{
    $clans = getAllClansAndNumbers();
    $membersRaw = getAllMemberHistories(count($clans));
    $membersNodes = $membersRaw["nodes"];
    $membersReferences = $membersRaw["reference"];

    $allNodes = array_merge($clans, array_flip($membersNodes));

    $references = array();
    foreach ($membersReferences as &$reference) {
        array_push($references, array($allNodes[$reference[0]],$allNodes[$reference[1]]));
    }

    foreach ($allNodes as $name => $number) {
        echo "{id: {$number}, label: '{$name}', x: 0, y: 0},\n";
    }

    echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";

    foreach ($references as &$reference) {
        echo "{from: {$reference[0]}, to: {$reference[1]}},\n";
    }
}
