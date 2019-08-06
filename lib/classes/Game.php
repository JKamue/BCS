<?php


class Game
{

    private $id;
    private $matchid;

    private $enemy;
    private $clan;
    private $map;
    private $lineup;

    private $win;
    private $elo;
    private $gametime;
    private $bac;

    public static function addGame($apiData) : Game
    {
        return new Game($apiData);
    }

    private function __construct($apiData)
    {
        // Create Enemy
        // Create Map
        // Create 4 Players
        // Create 4 Members
        // Create Lineup
        // Create Actual Game
    }

    public function save() {
        // Todo implement save for every object
    }


}