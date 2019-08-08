<?php


class Match
{

    private $id;
    private $matchid;

    private $enemy;
    private $clan;
    private $map;

    private $win;
    private $elo;
    private $gametime;
    private $bac;
    private $datetime;

    private $match_overview;
    private $match_stats;
    private $match_lineup;
    private $match_actions;

    /** @var $players Player[] */
    private $players = array();
    /** @var $members Member[] */
    private $members = array();
    private $lineup;

    public static function addGame($apidata, Clan $clan, Enemy $enemy, Map $map, Bool $winner) : Match
    {
        return new Match($apidata, $clan, $enemy, $map, $winner);
    }

    private function __construct($apidata, Clan $clan, Enemy $enemy, Map $map, Bool $winner)
    {
        $this->match_overview = $apidata[0];
        $this->match_stats = $apidata[1];
        $this->match_lineup = $apidata[2];
        $this->match_actions = $apidata[3];

        $this->clan = $clan;
        $this->enemy = $enemy;
        $this->map = $map;
        $this->winner = $winner;

        $this->generatePlayerList();
        $this->generateMemberList();
        $this->lineup = Lineup::createFromArray($this->clan, $this->members);
        $this->setMatchStats();
        // Feed Members
    }

    private function setMatchStats()
    {
        $this->matchid = $this->match_overview['matchid'];
        $this->id = md5($this->matchid() . $this->clan->id());

        $this->win = $this->winner === true ? 1 : 0;
        $this->elo = $this->match_stats['elo'];
        $this->gametime = $this->hourFormatToMin($this->match_stats['duration']);
        $this->datetime = $this->match_stats['datetime'];
    }

    private function hourFormatToMin($hourformat) : Int
    {
        $pieces = explode(":", $hourformat);
        if (count($pieces) == 3) {
            return $pieces[0] * 60 + $pieces[1];
        } else {
            return $pieces[0];
        }
    }

    public function generatePlayerList()
    {
        $this->bac = true;
        $status =  $this->winner === true ? "winner" : "loser";
        $lineup = $this->match_lineup[$status]['lineup'];
        $names = array();

        foreach ($lineup as &$set) {
            array_push($names, $set['name']);
            if (!$set['bac']) {
                $this->bac = false;
            }
        }

        $id_name_pairs = MojangApi::namesToUUID($names);

        foreach ($id_name_pairs as &$pair) {
            // Delete the name from the list (in case somebody changed names
            if (($key = array_search($pair['name'], $names)) !== false) {
                unset($names[$key]);
            }

            // Create class
            $player = Player::createFromArray($pair);
            array_push($this->players, $player);
        }

        foreach ($names as &$name) {
            $player = Player::createPlayerFromNameAndTime($name, $this->match_stats['datetime']);
            array_push($this->players, $player);
        }
    }

    public function generateMemberList()
    {
        foreach ($this->players as &$player) {
            $member = Member::create($this->clan, $player);
            $member->addCw($this->match_overview, $this->match_stats, $this->match_lineup, $this->match_actions, $this->winner);
            array_push($this->members, $member);
        }
    }


    public function matchid() : String
    {
        return $this->matchid;
    }

    public function timeAsString() : String
    {
        return $this->datetime;
    }

    public function save() {
        // TODO set clan last match and playtime
        $this->savePlayers();
        $this->saveMembers();
        $this->map->save();
        $this->enemy->save();
        $this->clan->save();
        $this->lineup->save();
        $this->saveGame();
    }

    private function saveGame() {
        $sql = "INSERT INTO game (GameID, Win, Elo, GameTime, BACGame, MapID, LineupID, EnemyUUID, ClanUUID, MatchID) VALUES  (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $data = array($this->id, $this->win, $this->elo, $this->gametime, $this->bac, $this->map->id(),
            $this->lineup->id(), $this->enemy->id(), $this->clan->id(), $this->matchid);
        Database::execute($sql,$data);
    }

    private function savePlayers()
    {
        foreach ($this->players as &$player) {
            $player->save();
        }
    }

    private function saveMembers()
    {
        foreach ($this->members as &$member) {
            $member->save();
        }
    }



}