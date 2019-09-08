<?php


class MemberRanker
{
    private $memberList = array();
    private $ranking = array();

    public function __construct(Int $minGames, Bool $onlyActive = true)
    {
        $this->memberList = $this->fetchList($minGames, $onlyActive);
    }

    private function numberList(array $list, bool $t= false) {
        $numberedList = array();

        for ($i = 1; $i <= count($list); $i++) {
            $player = $list[$i-1];
            $numberedList[$player["name"]] = $i;
            $numberedList[$player["uuid"]] = $i;
        }

        return $numberedList;
    }

    private function getSorted(array $list, string $sortFunction) {
        usort($list, array("MemberRanker",$sortFunction));
        return $this->numberList($list);
    }

    public function createAndSave(String $savePath) {
        $memberList = $this->memberList;

        $ranking['games'] = $this->getSorted($memberList,"rankByGames");
        $ranking['bac'] = $this->getSorted($memberList,"rankByBAC");
        $ranking['winlose'] = $this->getSorted($memberList,"rankByWinLose");
        $ranking['kd'] = $this->getSorted($memberList,"rankByKD");
        $ranking['beds'] = $this->getSorted($memberList,"rankByBeds");
        $ranking['suicide'] = $this->getSorted($memberList,"rankBySuicide");
        $ranking['quits'] = $this->getSorted($memberList,"rankByRagequits");

        file_put_contents($savePath,json_encode($ranking));
    }

    private function rankByGames($a, $b) {
        return $b["games"] - $a["games"];
    }

    private function rankByBAC($a, $b) {
        return $b["bac"] - $a["bac"];
    }

    private function rankByWinLose($a, $b) {
        $winloseA = $a["wins"] / $a["games"];
        $winloseB = $b["wins"] / $b["games"];
        return $winloseB > $winloseA;
    }

    private function rankByKD($a, $b) {
        if ($a['killed'] + $a['quits'] + $a['died'] != 0) {
            $kdA = $a['kills'] / ($a['killed'] + $a['quits'] + $a['died']);
        } else {
            $kdA = 100000;
        }

        if ($b['kills'] / ($b['killed'] + $b['quits'] + $b['died'] != 0)) {
            $kdB = $b['kills'] / ($b['killed'] + $b['quits'] + $b['died']);
        } else {
            $kdB = 100000;
        }
        return $kdB > $kdA;
    }

    private function rankByBeds($a, $b) {
        return $b["beds"] - $a["beds"];
    }

    private function rankByPlaytime($a, $b) {

    }

    private function rankBySuicide($a, $b) {
        return $b["died"] - $a["died"];
    }

    private function rankByRagequits($a, $b) {
        return $b["quits"] - $a["quits"];
    }

    private function fetchList(int $minGames, Bool $onlyActive)
    {
        $memberstats = "SELECT t.name as name, t.UUID as uuid, t.MVP as mvp, t.Betten as beds, t.Kills as kills, t.Killed as killed, t.Quits as quits, t.Died as died, t.BAC as bac, t.PlayerID as member,b.Games as games, b.Win as wins
FROM(SELECT player.name, member.MVP, member.Betten, member.Kills, member.Killed, member.Quits, member.Died, member.BAC, member.UUID, member.PlayerID
	FROM member
	JOIN player
	WHERE member.UUID= player.UUID
	AND member.Active = " . $onlyActive . "
	GROUP BY member.UUID) t
JOIN(SELECT member.PlayerID, COUNT(game.GameID) AS Games, SUM(game.Win) AS Win
	FROM lineup
	JOIN member
	ON member.PlayerID = lineup.Player1UUID OR member.PlayerID = lineup.Player2UUID OR member.PlayerID = lineup.Player3UUID OR member.PlayerID=lineup.Player4UUID
	JOIN game
	ON game.LineupID = lineup.LineupID
	GROUP BY member.PlayerID) b ON t.PlayerID= b.PlayerID
WHERE b.Games > " . $minGames . " GROUP BY t.PlayerID;";

        return Database::select($memberstats, array());
    }
}