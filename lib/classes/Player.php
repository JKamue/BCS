<?php


class Player
{
    public function createPlayerFromID(String $id)
    {
        // Todo name from mojang api
    }

    public function createPlayerFromNameAndTime(String $name, DateTime $time)
    {
        // Todo get name from mojang api
    }

    public function createPlayerFromName(String $name)
    {
        // Todo most recent person with taht name from namemc
    }

    private $id;
    private $name;

    private function __construct(String $id, $name)
    {

    }

    public function id() : String
    {
        return $this->id;
    }

    public function name() : String
    {
        return $this->name;
    }

    public function save() {
        // Todo implement
    }
}