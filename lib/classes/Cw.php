<?php


class Cw
{
    private $info;
    private $stats;
    private $lineup;
    private $actions;
    /** @var $clans Clan[] */
    private $clans;
    /** @var $clans Match[] */
    private $matches;

    public static function getMatch(Array $matchinfo, Array $clans = array()) : Cw
    {
        $stats = GommeApi::fetchCwStats($matchinfo['matchid']);
        $lineup = GommeApi::fetchCwPlayers($matchinfo['matchid']);
        $actions = GommeApi::fetchCwActions($matchinfo['matchid']);
        return new Cw($matchinfo, $stats, $lineup, $actions, $clans);
    }

    private function __construct($info, $stats, $lineup, $actions, $clans)
    {
        $this->info = $info;
        $this->stats = $stats;
        $this->lineup = $lineup;
        $this->actions = $actions;
        $this->clans = $clans;
    }

    private function api() : Array
    {
        return array($this->info, $this->stats, $this->lineup, $this->actions);
    }

    public function compute()
    {
        $bothNoBCS = true;
        $options = array("winner", "loser");
        $options_inv = array("loser", "winner");

        /** @var $clans Clan[] */
        $clans = $this->getClanlist($options);

        $map = Map::getOrCreate($this->info['map']);

        foreach ($options as $key => $option) {
            $clan = $clans[$option];
            $enemy = $clans[$options_inv[$key]]->toEnemy();
            $stat = $option === "winner" ? true : false;

            if ($clan->isInBCs())
            {
                // TODO also check if CW is considered old
                $bothNoBCS = false;
                $match = Match::addGame($this->api(), $clan, $enemy, $map, $stat);
                $this->matches[$option] = $match;
            }
        }

        // Update both clans as enemys in BCS so api resources are not wasted
        if ($bothNoBCS) {
            $this->saveBothEnemies();
        }
    }

    private function getClanlist(Array $options) : Array
    {
        /** @var $clans Clan[] */
        $clans = array();
        foreach ($options as &$option) {
            $clan = $this->getClan($this->info[$option]);
            $clans[$option] = $clan;
        }
        return $clans;
    }

    private function getClan($name) : Clan
    {
        // Check if clan was sent in parameters
        foreach ($this->clans as &$clan) {
            if ($clan->name() == $name) {
                return $clan;
            }
        }

        // Get Clan from Gomme and or DB
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