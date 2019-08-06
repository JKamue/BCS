<?php


class Clan
{
    public static function getOrCreateClan() : Clan
    {
        // Todo look if clan in db or create new clan
    }

    private $id;
    private $name;

    private $tag;
    private $added;
    private $updated;
    private $active;
    private $match;

    private function __construct($id, $name, $tag, $added, $updated, $active, $match)
    {
        $this->id = $id;
        $this->name = $name;
        $this->tag = $tag;
        $this->added = $added;
        $this->updated = $updated;
        $this->active = $active;
        $this->match = $match;
    }

    public function id() : String {
        return $this->id;
    }

    public function name() : String {
        return $this->name;
    }

    public function save() {
        // Todo implement
    }


}