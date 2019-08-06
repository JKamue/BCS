<?php


class Map
{
    public static function getOrCreate(String $name): Map
    {
        // TODO Look if map exists and Generate Map object
    }

    private $name;
    private $id;

    private function __construct(String $id, String $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    private function id() : String {
        return $this->id;
    }

    private function name() : String {
        return $this->name;
    }

    public function save() {
        // Todo implement
    }
}