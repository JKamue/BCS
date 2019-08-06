<?php


class Enemy
{
    public static function createEnemy(String $uuid, String $tag, String $name): Enemy
    {
        return new Enemy($uuid, $tag, $name);
    }

    public static function getEnemyFromDatabase(): Enemy
    {
        // Todo implement
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
        // Todo Implement
    }
}