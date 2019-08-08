<?php


class Member
{


    private $id;

    private $active = 0;
    private $mvp = 0;
    private $beds = 0;
    private $kills = 0;
    private $killed = 0;
    private $quits = 0;
    private $died = 0;
    private $bac = 0;

    public $clan;
    public $player;

    public static function create(Clan $clan, Player $player) : Member
    {
        $id = md5($clan->id() . $player->id());

        $active = 1;
        $mvp = $beds = $kills = $killed = $quits = $died = $bac = 0;

        return new Member($id, $active, $mvp, $beds, $kills, $killed, $quits, $died, $bac, $clan, $player);
    }

    private function __construct(String $id, Int $active, Int $mvp, Int $beds, Int $kills, Int $killed, Int $quits, Int $died, Int $bac, Clan $clan, Player $player)
    {
        $this->id = $id;
        $this->active = $active;

        $this->mvp = $mvp;

        $this->beds = $beds;
        $this->kills = $kills;
        $this->killed = $killed;
        $this->quits = $quits;
        $this->died = $died;

        $this->bac = $bac;

        $this->clan = $clan;
        $this->player = $player;
    }


    public function addCw(Array $match_overview, Array $match_stats, Array $match_lineup, Array $match_actions, Bool $won)
    {
        $status = $won === true ? "winner" : "loser";

        $this->checkIfMVP($match_stats);
        $this->addActions($match_actions);
        $this->checkIfBac($match_lineup, $status);
    }

    private function checkIfBac($match_lineup, $status)
    {
        foreach ($match_lineup[$status]['lineup'] as &$player) {
            if ($player['name'] == $this->player->name() and $player['bac'] == true) {
                $this->bac += 1;
            }
        }
    }

    private function addActions($match_actions)
    {
        $name = $this->player->name();
        foreach ($match_actions as &$action) {
            if ($action['action'] == "quit") {
                if ($action['subject'] == $name) {
                    $this->quits += 1;
                }
            } else if ($action['action'] == "joined") {
                if ($action['subject'] == $name) {
                    $this->quits -= 1;
                }
            } else if ($action['action'] == "destroyed") {
                if ($action['subject'] == $name) {
                    $this->beds += 1;
                }
            } else if ($action['action'] == "killed") {
                if ($action['subject'] == $name) {
                    $this->kills += 1;
                } else if ($action['object'] == $name) {
                    $this->killed += 1;
                }
            } else if ($action['action'] == "died") {
                if ($action['subject'] == $name) {
                    $this->died += 1;
                }
            }
        }
    }

    private function checkIfMVP($match_stats)
    {
        if ($match_stats['mvp'] == $this->player->name()) {
            $this->mvp += 1;
        }
    }

    public function id() : String
    {
        return $this->id;
    }

    public function save()
    {
        $sql = "SELECT UUID FROM member WHERE PlayerID = ?";
        $stm = Database::execute($sql, array($this->id));

        if ($stm->rowCount() == 1) {
            $sql = "UPDATE member SET 
                  Active = ?,
                  MVP = MVP + ?,
                  Kills = Kills + ?,
                  Killed = Killed + ?,
                  Quits = Quits + ?,
                  Died = Died + ?,
                  BAC = BAC + ?
                WHERE PlayerID = ?";
            $data = array($this->active, $this->mvp, $this->kills, $this->killed, $this->quits, $this->died, $this->bac, $this->id);
        } else {
            $this->player->save();

            $sql = "INSERT INTO member 
                (PlayerID, Active, MVP, Betten, Kills, Killed, Quits, Died, ClanUUID, UUID, BAC) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $data = array($this->id, $this->active, $this->mvp, $this->beds, $this->kills, $this->killed, $this->quits, $this->died, $this->clan->id(), $this->player->id(), $this->bac);
        }
        Database::execute($sql, $data);
    }
}