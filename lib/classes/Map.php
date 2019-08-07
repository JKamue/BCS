<?php


class Map
{
    public static function getNewId() : Int
    {
        $id = file_get_contents(ROOT . "data/tmp/mapcounter.txt");
        $id++;
        file_put_contents(ROOT . "data/tmp/mapcounter.txt", $id);
        return $id;
    }

    public static function getOrCreate(String $name): Map
    {
        $sql = "SELECT MapID FROM map WHERE MapName = ?";
        $map_data = Database::execute($sql, array($name));

        if ($map_data->rowCount() != 1) {
            $id = self::getNewId();
            $map = new Map($id, $name);
            $map->save();
        } else {
            $map_data = $map_data->fetchAll()[0];
            $map = new Map($map_data['id'], $name);
        }
        return $map;
    }

    private $name;
    private $id;

    private function __construct(String $id, String $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    private function id() : String
    {
        return $this->id;
    }

    private function name() : String
    {
        return $this->name;
    }

    private function save()
    {
        $sql = "INSERT INTO map (MapID, MapName) VALUES  (?, ?)";
        Database::execute($sql, array($this->id, $this->name));
    }
}