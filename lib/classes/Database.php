<?php

class Database
{
    /**
     * Creates a new Database instance
     *
     * @param $name The name of the database (saved in the config)
     * @return PDO The database instance
     */
    public static function getConnection($name) : PDO {
        $cred = DBConfig::db()[$name];
        return new PDO('mysql:host=' . $cred['host'] . ';dbname='.$cred['name'].';charset=utf8',
            $cred['user'] , $cred['pass'] );
    }
}