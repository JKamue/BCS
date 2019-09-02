<?php

interface iGameCollector
{
    public function compute();
    public function save();
}

class InvalidCw implements iGameCollector
{
    public function __construct()
    {

    }

    public function compute() {

    }

    public function save() {

    }
}

class Cw implements iGameCollector
{
    private $info;
    private $stats;
    private $lineup;
    private $actions;
    /** @var $clans Clan[] */
    private $clans = array();
    /** @var $clans Clan[] */
    private $givenClans;
    /** @var $clans Match[] */
    private $matches = array();

    private $clanWasSet = array();

    public static function getMatch(Array $matchinfo, Array $clans = array()) : iGameCollector
    {
        $stats = GommeApi::fetchCwStats($matchinfo['matchid']);
        if ($stats == false) {
            return new InvalidCw();
        }
        $lineup = GommeApi::fetchCwPlayers($matchinfo['matchid']);
        $actions = GommeApi::fetchCwActions($matchinfo['matchid']);
        echo "Received valid information for CW " . $matchinfo['matchid']. "<br>";
        return new Cw($matchinfo, $stats, $lineup, $actions, $clans);
    }

    private function __construct($info, $stats, $lineup, $actions, $clans)
    {
        $this->info = $info;
        $this->stats = $stats;
        $this->lineup = $lineup;
        $this->actions = $actions;
        $this->givenClans = $clans;
    }

    private function api() : Array
    {
        return array($this->info, $this->stats, $this->lineup, $this->actions);
    }

    private function cwNotOld(Clan $clan)
    {
        $sql = "SELECT LastActive, LastMatch FROM clan WHERE ClanUUID = ?";
        $res = Database::selectFirst($sql, array($clan->id()));
        if ($res['LastActive'] >= $this->info['datetime']) {
            return false;
        } else {
            return true;
        }
    }

    public function compute()
    {
        $bothNoBCS = true;
        $options = array("winner", "loser");
        $options_inv = array("loser", "winner");

        /** @var $clans Clan[] */
        $this->getClanlist($options);

        $map = Map::getOrCreate($this->info['map']);

        foreach ($options as $key => $option) {
            $clan = $this->clans[$option];
            $enemy = $this->clans[$options_inv[$key]]->toEnemy();
            $stat = $option === "winner" ? true : false;

            if ($clan->isInBCs())
            {
                $bothNoBCS = false;
                echo $clan->name() . "is in BCS" . "<br>";

                if ($this->cwNotOld($clan)) {
                    $match = Match::addGame($this->api(), $clan, $enemy, $map, $stat, $this->clanWasSet);
                    $this->matches[$option] = $match;
                } else {
                    echo $clan->name() . "old CW". "<br>";
                }
            } else {
                echo $clan->name() . "is not in BCS". "<br>";
            }
        }

        // Update both clans as enemys in BCS so api resources are not wasted
        if ($bothNoBCS) {
            $this->saveBothEnemies();
        }
    }

    private function getClanlist(Array $options)
    {
        /** @var $clans Clan[] */
        $clans = array();
        foreach ($options as &$option) {
            $clan = $this->getClan($this->info[$option]);
            $clans[$option] = $clan;
        }
        $this->clans = $clans;
    }

    private function getClan($name) : Clan
    {
        // Check if clan was sent in parameters
        foreach ($this->givenClans  as &$clan) {
            if ($clan->name() == $name) {
                $this->clanWasSet[$name] = true;
                return $clan;
            }
        }

        // Get Clan from Gomme and or DB
        $this->clanWasSet[$name] = false;
        return Clan::getOrCreateClanByName($name);
    }

    private function saveBothEnemies()
    {
        $this->clans['winner']->toEnemy()->save();
        $this->clans['loser']->toEnemy()->save();
    }

    public function save() {
        foreach ($this->matches as &$match) {
            $match->save();
        }
    }

}