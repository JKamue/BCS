<?php

function getRanking($number) {
    $list = json_decode(file_get_contents("../../data/tmp/ranking.json"), true);
    $tmp['games'] = getTopPlayer($list['games'], $number);
    $tmp['bac'] = getTopPlayer($list['bac'], $number);
    $tmp['winlose'] = getTopPlayer($list['winlose'], $number);
    $tmp['kd'] = getTopPlayer($list['kd'], $number);
    $tmp['beds'] = getTopPlayer($list['beds'], $number);
    $tmp['suicide'] = getTopPlayer($list['suicide'], $number);
    $tmp['quits'] = getTopPlayer($list['quits'], $number);
    $tmp['mvp'] = getTopPlayer($list['mvp'], $number);

    return $tmp;
}

function getTopPlayer($array, $number) {
    for ($i = 0; $i < $number; $i++) {
        $result[$i] = [
            "name" => "not found",
            "uuid" => "not found",
        ];
    }

    foreach ($array as $entry => $rank) {
        $rank -= 1;
        if ($rank < $number) {
            if (strlen($entry) == 32) {
                $result[$rank]["uuid"] = $entry;
            } else {
                $result[$rank]["name"] = $entry;
            }
        }
    }

    $result["keyholder"] = true;

    return $result;
}

function getAllClanstats() {
    $sql = "SELECT clan.ClanUUID As uuid, clan.ClanTag As tag, clan.ClanName As Name, clan.DateAdded As added, clan.DateUpdated As updated, clan.LastActive as active, clan.LastMatch as last,
                        COUNT(game.GameID) AS games, SUM(game.Win) As Wins, SUM(BACGame) As Bac, SUM(game.Elo) As Elo, SUM(game.GameTime) As time, SUM(CASE WHEN game.GameTime > 65 THEN 1 ELSE 0 END) As dms
                From clan
                JOIN game On clan.ClanUUID = game.ClanUUID
                Group By ClanName  
                ORDER BY `dms`  DESC";
    return Database::select($sql, array());
}

function bcsSearchClan($term)
{
    if (strlen($term) < 3) { return "";}
    return Database::select("SELECT ClanUUID, ClanName FROM `clan` WHERE LOWER(`ClanName`) LIKE LOWER(?)",array("%".$term."%"));
}

function bcsSearchPlayer($term) {
    if (strlen($term) < 3) { return "";}
    $sql = "SELECT member.UUID, player.name FROM `member`
JOIN player on member.UUID = player.UUID
WHERE LOWER(player.name) LIKE LOWER(?) GROUP BY player.name";

    return Database::select($sql,array("%".$term."%"));
}

function playerNameToUUID($name) {
    $sql = "SELECT UUID FROM `player` WHERE name = ?";
    return Database::select($sql,[$name]);
}

function getAllClanData($uuid) : Array
{
    $clanstats = getClanStats($uuid);
    $memberstats = getMemberStats($uuid);
    $mapstats = getMapStats($uuid);
    $enemystats = getEnemyStats($uuid);
    $allLineupstats = getLineupStats($uuid);

    $relevantLimeupStats = array();

    $allMember = getAllMember($uuid);

    // Take only active lineups
    foreach ($allLineupstats as &$lineup) {
        $active = true;

        $players = array('Player1', 'Player2', 'Player3', 'Player4');

        foreach ($players as &$player) {
            foreach ($allMember as &$member) {
                if ($member['PlayerID'] == $lineup[$player . "UUID"]) {
                    $lineup[$player] = $member['name'];
                    $lineup[$player . "UUID"] = $member['uuid'];
                    if ($member['Active'] != true) {
                        $active = false;
                    }
                }
            }
        }

        $lineup['active'] = $active;
        //$lineup['games'] > 3 &&
        if ($lineup['active']) {
            array_push($relevantLimeupStats, $lineup);
        }

    }

    // Mark BCS Clans
    $bcsClans = getAllClans();
    $completeEnemyStats = array();
    foreach ($enemystats as $enemy) {
        $enemy['bcs'] = false;
        foreach ($bcsClans as $clan) {
            if ($clan['ClanUUID'] == $enemy['uuid']) {
                $enemy['bcs'] = true;
                continue;
            }
        }
        array_push($completeEnemyStats, $enemy);
    }


    $return = array();
    $return['clan'] = $clanstats;
    $return['member'] = $memberstats;
    $return['maps'] = $mapstats;
    $return['enemy'] = $completeEnemyStats;
    $return['lineupstats'] = $relevantLimeupStats;




    return $return;
}

function getClanStats($uuid)
{
    $clanstats = "SELECT clan.ClanUUID As uuid, clan.ClanTag As tag, clan.ClanName As Name, clan.DateAdded As added, clan.DateUpdated As updated, clan.LastActive as active, clan.LastMatch as last,
		COUNT(game.GameID) AS games, SUM(game.Win) As Wins, SUM(BACGame) As Bac, SUM(game.Elo) As Elo, SUM(game.GameTime) As time, SUM(CASE WHEN game.GameTime >= 65 THEN 1 ELSE 0 END) As dms
From game
JOIN clan
WHERE game.ClanUUID = ? AND clan.ClanUUID = ? ;";

    return Database::select($clanstats, array($uuid, $uuid));
}

function getMemberStats($uuid)
{
    $memberstats = "SELECT t.name as name, t.UUID as uuid, t.MVP as mvp, t.Betten as beds, t.Kills as kills, t.Killed as killed, t.Quits as quits, t.Died as died, t.BAC as bac, t.PlayerID as member,b.Games as games, b.Win as wins
FROM(SELECT player.name, member.MVP, member.Betten, member.Kills, member.Killed, member.Quits, member.Died, member.BAC, member.UUID, member.PlayerID
	FROM member
	JOIN player
	WHERE member.UUID= player.UUID
	AND member.Active = TRUE
    AND member.ClanUUID = ?
	GROUP BY member.UUID) t
JOIN(SELECT member.PlayerID, COUNT(game.GameID) AS Games, SUM(game.Win) AS Win
	FROM lineup
	JOIN member
	ON member.PlayerID = lineup.Player1UUID OR member.PlayerID = lineup.Player2UUID OR member.PlayerID = lineup.Player3UUID OR member.PlayerID=lineup.Player4UUID
	JOIN game
	ON game.LineupID = lineup.LineupID
    	WHERE member.ClanUUID = ?
	GROUP BY member.PlayerID) b ON t.PlayerID= b.PlayerID
GROUP BY t.PlayerID;";

    return Database::select($memberstats, [$uuid, $uuid]);
}

function getMapStats($uuid)
{
    $mapsstats = "SELECT map.MapName AS name, COUNT(game.GameID) AS games, SUM(game.Win) AS wins, SUM(IF(game.GameTime>60,1,0)) AS dms
FROM map
	JOIN game
   	WHERE map.MapID = game.MapID AND game.ClanUUID = ?
   	GROUP BY map.MapName  
ORDER BY `Games`  DESC;";

    return Database::select($mapsstats, array($uuid));
}

function getEnemyStats($uuid)
{
    $enemystats = "SELECT enemy.ClanName as name, enemy.ClanTag as tag, enemy.EnemyUUID as uuid, COUNT(game.GameID) AS games, SUM(game.Win) AS wins
FROM enemy
   	JOIN game
   	WHERE enemy.EnemyUUID = game.EnemyUUID AND game.ClanUUID = ?
   	GROUP BY enemy.ClanName;";

    return Database::select($enemystats, array($uuid));
}

function getLineupStats($uuid)
{
    $lineupstats = "SELECT lineup.LineupID,lineup.Player1UUID,lineup.Player2UUID, lineup.Player3UUID, lineup.Player4UUID, COUNT(game.GameID) AS games, SUM(game.Win) As wins, SUM(BACGame) As bac, SUM(game.Elo) As elo, SUM(game.GameTime) As time, SUM(CASE WHEN game.GameTime > 60 THEN 1 ELSE 0 END) As dms
	FROM lineup
    JOIN game on lineup.LineupID = game.LineupID
	WHERE lineup.ClanUUID = ?
    GROUP BY lineup.LineupID";

    return Database::select($lineupstats, array($uuid));
}

function getAllMember($uuid)
{
    $allmember = "SELECT member.PlayerID, member.Active, player.name, player.UUID as uuid
FROM member
JOIN player ON member.UUID = player.UUID
WHERE ClanUUID = ?";

    return Database::select($allmember, array($uuid));
}

function getAllClans() {
    $allBcsClanRequest = "SELECT ClanUUID FROM `clan`";
    return Database::select($allBcsClanRequest,array());
}

function getBCSStats()
{
    // Anzahl an Clans
    $clanAmountRequest = "SELECT count(ClanUUID) as ClanAmount FROM `clan`";
    // Anzahl an Spielern
    $playerAmountRequest = "SELECT count(PlayerID) as PlayerAmount FROM `member` WHERE Active = 1";
    // Anzahl matches und count spielzeit
    $gameStatsRequest = "SELECT count(GameID) as GameAmount, sum(GameTime) as GameTime FROM `game`";
    // Anzahl Lineups
    $lineupAmountRequest = "SELECT count(LineupID) as LineupAmount FROM `lineup`";

    $response = array();
    $response['clans'] = Database::selectFirst($clanAmountRequest,array())['ClanAmount'];
    $response['players'] = Database::selectFirst($playerAmountRequest,array())['PlayerAmount'];

    $data = Database::selectFirst($gameStatsRequest,array());
    $response['games'] = $data['GameAmount'];
    $response['time'] = round($data['GameTime']/60);

    $response['lineups'] = Database::selectFirst($lineupAmountRequest,array())['LineupAmount'];

    return $response;
}

function getPlayerStats($playeruuid) {
    $memberhistory = "SELECT t.name as name, t.UUID as uuid, t.MVP as mvp, t.Betten as beds, t.Kills as kills, t.Killed as killed, t.Quits as quits, t.Died as died, t.BAC as bac, t.PlayerID as member,b.Games as games, b.Win as wins, t.Active as active, clan.ClanName as clan, clan.ClanTag as tag
        FROM(SELECT player.name, member.MVP, member.Betten, member.Kills, member.Killed, member.Quits, member.Died, member.BAC, member.UUID, member.PlayerID, member.ClanUUID, member.Active
            FROM member
            JOIN player
            WHERE member.UUID = player.UUID
            GROUP BY member.PlayerID) t
        JOIN(SELECT member.PlayerID, COUNT(game.GameID) AS Games, SUM(game.Win) AS Win
            FROM lineup
            JOIN member
            ON member.PlayerID = lineup.Player1UUID OR member.PlayerID = lineup.Player2UUID OR member.PlayerID = lineup.Player3UUID OR member.PlayerID=lineup.Player4UUID
            JOIN game
            ON game.LineupID = lineup.LineupID
                WHERE member.UUID = ?
            GROUP BY member.PlayerID) b ON t.PlayerID= b.PlayerID
        JOIN clan ON clan.ClanUUID = t.ClanUUID";

    $list = json_decode(file_get_contents("../../data/tmp/ranking.json"), true);
    if (!isset($list["games"][$playeruuid])) {
        $tmp['history']['error'] = true;
    } else {
        $tmp['games'] = $list['games'][$playeruuid];
        $tmp['bac'] = $list['bac'][$playeruuid];
        $tmp['winlose'] = $list['winlose'][$playeruuid];
        $tmp['kd'] = $list['kd'][$playeruuid];
        $tmp['beds'] = $list['beds'][$playeruuid];
        $tmp['suicide'] = $list['suicide'][$playeruuid];
        $tmp['quits'] = $list['quits'][$playeruuid];
        $tmp['mvp'] = $list['mvp'][$playeruuid];
    }

    $tmp['clans'] = Database::select($memberhistory, array($playeruuid));
    return $tmp;
}