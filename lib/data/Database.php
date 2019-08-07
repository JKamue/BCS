<?php

class Database
{
    /**
     * Creates a new Database instance
     *
     * @param $name String name of the database (saved in the config)
     * @return PDO The database instance
     */
    private static function getConnection(String $name) : PDO
    {
        $cred = DBConfig::db()[$name];
        return new PDO('mysql:host=' . $cred['host'] . ';dbname='.$cred['name'].';charset=utf8',
            $cred['user'] , $cred['pass'] );
    }

    /**
     * Executes the Statement and returns the result
     *
     * @param String The MySQL statement
     * @param array An array with the values
     * @return PDOStatement The executed statement
     */
    public static function execute(String $stm, Array $array) : PDOStatement
    {
        $pdo = self::getConnection("main");
        $stm = $pdo->prepare($stm);
        $stm->execute($array);
        return $stm;
    }

    /**
     * Counts the amount of lines a statement returns
     *
     * @param String The MySQL statement
     * @param array An array with the values
     * @return Int Amount of Lines
     */
    public static function count(String $stm, Array $array) : Int
    {
        return self::execute($stm, $array)->rowCount();
    }

    /**
     * Executes a Select Statement and returns all lines
     *
     * @param String The MySQL statement
     * @param array An array with the values
     * @return array All lines
     */
    public static function select(String $stm, Array $array) : Array
    {
        return self::execute($stm, $array)->fetchAll();
    }

    /**
     * Returns the first line of the Statements result
     *
     * @param String The MySQL statement
     * @param array An array with the values
     * @return array The first Line of the result
     */
    public static function selectFirst(String $stm, Array $array) : Array
    {
        return self::execute($stm, $array)->fetchAll()[0];
    }

    public static function parseDatetime(String $string) : DateTime
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', $string);
    }
}