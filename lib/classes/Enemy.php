<?php


class Enemy
{
    public static function createEnemy(String $uuid, String $tag, String $name): Enemy
    {
        return new Enemy($uuid, $tag, $name);
    }

    private $uuid;
    private $tag;
    private $name;

    private function __construct(String $uuid, String $tag, String $name)
    {
        $this->uuid = $uuid;
        $this->tag = $tag;
        $this->name = $name;
    }

    public function save()
    {
        $sql = "SELECT ClanName FROM enemy WHERE EnemyUUID = ?";
        $stm = Database::execute($sql, array($this->uuid));

        if ($stm->rowCount() == 1) {
            if ($stm->fetchAll()[0]['ClanName'] != $this->name) {
                $sql = "UPDATE enemy SET ClanName = ?, ClanTag = ? WHERE EnemyUUID = ?";
                Database::execute($sql, array($this->name, $this->tag, $this->uuid));
            }
        } else {
            $sql = "INSERT INTO enemy (EnemyUUID, ClanTag, ClanName) VALUES (?, ?, ?)";
            Database::execute($sql, array($this->uuid, $this->tag, $this->name));
        }
    }
}