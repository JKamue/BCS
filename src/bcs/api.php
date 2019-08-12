<?php

function getAllClanData($uuid) : Array
{
    $clanstats = getClanStats($uuid);
    $memberstats = getMemberStats($uuid);
    $mapstats = getMapStats($uuid);
    $enemystats = getEnemyStats($uuid);
    $lineupstats = getLineupStats($uuid);

    $allMember = getAllMember($uuid);
    foreach ($lineupstats as &$lineup) {
        $active = true;

        $players = array('Player1', 'Player2', 'Player3', 'Player4');

        foreach ($players as &$player) {
            foreach ($allMember as &$member) {
                if ($member['PlayerID'] == $lineup[$player . "UUID"]) {
                    $lineup[$player] = $member['name'];
                    if ($member['Active'] != true) {
                        $active = false;
                    }
                }
            }
        }

        $lineup['active'] = $active;


    }

    $return = array();
    $return['clan'] = $clanstats;
    $return['member'] = $memberstats;
    $return['maps'] = $mapstats;
    $return['enemy'] = $enemystats;
    $return['lineupstats'] = $lineupstats;




    return $return;
}

function getClanStats($uuid)
{
    $clanstats = "SELECT clan.ClanUUID As uuid, clan.ClanTag As tag, clan.ClanName As Name, clan.DateAdded As added, clan.DateUpdated As updated, clan.LastActive as active, clan.LastMatch as last,
		COUNT(game.GameID) AS games, SUM(game.Win) As Wins, SUM(BACGame) As Bac, SUM(game.Elo) As Elo, SUM(game.GameTime) As time, SUM(CASE WHEN game.GameTime > 60 THEN 1 ELSE 0 END) As dms
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

    return Database::select($memberstats, array($uuid));
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
    $allmember = "SELECT member.PlayerID, member.Active, player.name
FROM member
JOIN player ON member.UUID = player.UUID
WHERE ClanUUID = ?";

    return Database::select($allmember, array($uuid));
}