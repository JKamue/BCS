<?php


function checkMemberStats($playername) {
    $sql = "SELECT * FROM `player`
            JOIN `member` ON member.UUID = player.UUID AND member.Active = 1
            WHERE player.name = ?";
    $list = json_decode(file_get_contents("../../data/tmp/ranking.json"), true);
    if (isset($list['games'][$playername])) {
        $playerid = Database::selectFirst($sql, array($playername))['PlayerID'];
        $sql2 = "SELECT t.name as name, t.UUID as uuid, t.MVP as mvp, t.Betten as beds, t.Kills as kills, t.Killed as killed, t.Quits as quits, t.Died as died, t.BAC as bac, t.PlayerID as member,b.Games as games, b.Win as wins
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
                WHERE member.PlayerID = ?
            GROUP BY member.PlayerID) b ON t.PlayerID= b.PlayerID
        GROUP BY t.PlayerID;";

        $player = Database::selectFirst($sql2, array($playerid));

        $winlose = round($player["wins"] / $player["games"],2)*100;
        $kd = round($player['kills'] / ($player['killed'] + $player['quits'] + $player['died']),2 );

        echo "Stats von $playername<br>";
        echo "Spiele: " . $list['games'][$playername] . ", ". $player['games'] ."<br>";
        echo "BAC-Spiele: " . $list['bac'][$playername] . ", ". $player['bac'] ."<br>";
        echo "Winlose: " . $list['winlose'][$playername] . ", ". $winlose ."%<br>";
        echo "KD: " . $list['kd'][$playername] . ", ". $kd ."<br>";
        echo "Betten: " . $list['beds'][$playername] . ", ". $player['beds'] ."<br>";
        echo "Selbstmorde: " . $list['suicide'][$playername] . ", ". $player['died'] ."<br>";
        echo "Ragequit: " . $list['quits'][$playername] . ", ". $player['quits'] ."<br>";
    } else {
        if (Database::count($sql, array($playername)) == 0) {
            echo "Spieler ist nicht in BCS";
        } else {
            echo "Spieler hat nicht genug Spiele gespielt";
        }
    }
}

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