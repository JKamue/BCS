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
    $sql = "SELECT clan.ClanName, player.name, member.Active FROM `member`
	JOIN clan ON clan.ClanUUID = member.ClanUUID
	JOIN player ON player.UUID = member.UUID";

    $return = [];
    $return["reference"] = array();
    $data = Database::select($sql,[]);
    $i+=10;
    foreach ($data as &$player) {
        array_push($return["reference"], array($player["name"], $player["ClanName"], $player["Active"]));
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
        if (isset($allNodes[$reference[0]]) && isset($allNodes[$reference[1]])) {
            array_push($references, array($allNodes[$reference[0]], $allNodes[$reference[1]], $reference[2] == 1 ? 2 : 0.5));
        }
    }

    return [
        "nodes" => $allNodes,
        "edges" => $references,
        "clan_amount" => count($clans)
    ];
}
