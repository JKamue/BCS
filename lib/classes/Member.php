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

    public static function getOrCreate(Clan $clan, Player $player): Member
    {
        // Todo calculate id and construct
    }

    private function __construct(Clan $clan, Player $player)
    {

    }

    public function id() : String {
        return $this->id();
    }

    public function save() {
        // Todo implement
    }
}