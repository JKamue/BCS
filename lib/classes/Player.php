<?php


class Player
{
    public function createPlayerFromID(String $id) : Player
    {
        $name = MojangApi::UUIDToName($id);
        return new Player($id, $name);
    }

    public function createPlayerFromNameAndTime(String $name, $time) : Player
    {
        $idAtTime = MojangApi::NameToUUUIDAtDateTime($name, $time);
        return new Player($idAtTime, $name);
    }

    public function createPlayerFromName(String $name) : Player
    {
        $id_guess = NameMcApi::oldNameToUUID($name);
        return new Player($id_guess, $name);
    }

    private $id;
    private $name;

    private function __construct(String $id, String $name)
    {
        $this->id = $id;
        $this->name = $name;
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
        $sql = "SELECT name FROM player WHERE UUID = ?";
        $data = Database::execute($sql, array($this->id));
        $count = $data->rowCount();

        if ($count == 0) {
            $sql = "INSERT INTO player (UUID, name) VALUES (?, ?)";
            Database::execute($sql, array($this->id, $this->name));
        } else {
            if ($data->fetchAll()[0]['name'] != $this->name) {
                $sql = "UPDATE player SET name = ? WHERE UUID = ?";
                Database::execute($sql, array($this->name, $this->id));
            }
        }

    }
}