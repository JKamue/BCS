<?php


class Clan
{
    /**
     * Reads Clan from DB or Creates a new one if no Clan exists
     *
     * @param String $name The name of the Clan
     * @return Clan Clan object
     */
    public static function getOrCreateClanByName(String $name) : Clan
    {
        self::getOrCreateClanByClanStats(GommeApi::fetchClanStats($name));
    }

    /**
     * Reads Clan from DB or Creates a new one if no Clan exists
     *
     * @param array $cs The Stats from the gommeApi
     * @return Clan Clan object
     */
    public static function getOrCreateClanByClanStats(Array $cs) : Clan
    {
        $sql = "SELECT DateAdded, DateUpdated, LastActive, LastMatch FROM clan WHERE ClanUUID = ?";
        $stm = Database::execute($sql, $cs['id']);

        $now = date('Y-m-d H:i:s');

        if ($stm->rowCount() == 1) {
            $data = $stm->fetchAll([0]);
            $added = Database::parseDatetime($data['DateAdded']);
            $updated = $now;
            $active = Database::parseDatetime($data['LastActive']);
            $match = $data['LastMatch'];
        } else {
            $added = $now;
            $updated = $now;
            $active = $now;
            $match = "notplayed";
        }

        return new Clan($cs['uuid'], $cs['name'], $cs['tag'], $added, $updated, $active, $match);
    }

    /**
     * Checks if Clan ID exists on GommeHD.net
     *
     * @param String $id Clan id to check
     * @return bool True if it exists
     */
    public static function clanIDExists(String $id) : Bool
    {
        $name = GommeApi::convertUUIDtoName($id);
        return self::checkIfClanNameExists($name);
    }

    /**
     * Checks if Clan ID exists on GommeHD.net
     *
     * @param String $name The name of the clan
     * @return bool True if the clan exists
     */
    public static function clanNameExists(String $name) : Bool
    {
        if (GommeApi::fetchClanStats($name) === false) {
            return false;
        }
        return true;
    }

    /** Checks if a clan already Exists in BCS */
    public static function idInBCS(String $id) : Bool
    {
        $sql = "SELECT ClanName FROM clan WHERE ClanUUID = ?";
        $count = Database::count($sql, array($id));
        if ($count === 0) {
            return false;
        }
        return true;
    }

    /** Checks if a clan already Exists in BCS */
    public static function nameInBCS(String $name) : Bool
    {
        $sql = "SELECT ClanName FROM clan WHERE ClanName = ?";
        $count = Database::count($sql, array($name));
        if ($count === 0) {
            return false;
        }
        return true;
    }

    private $id;
    private $name;

    private $tag;
    private $added;
    private $updated;
    private $active;
    private $match;

    private function __construct(String $id, String $name, String $tag, DateTime $added, DateTime $updated, DateTime $active, String $match)
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

    public function setLastMatch(Game $game) : Game
    {
        $this->match = $game->matchid();
        $this->active = Database::parseDatetime($game->timeAsString());
    }

    public function save() {
        $sql = "SELECT ClanName FROM clan WHERE ClanUUID = ?";
        $exists = Database::count($sql, array($this->id));

        if ($exists == 1) {
            $sql = "UPDATE clan SET ClanName = ?, ClanTag = ?, DateUpdated = ?, LastActive = ?, LastMatch = ? WHERE ClanUUID = ?";
            $array = array($this->name, $this->tag, $this->updated, $this->active, $this->match, $this->id);
        } else {
            $sql = "INSERT INTO clan (ClanUUID, ClanTag, ClanName, DateAdded, DateUpdated, LastActive, LastMatch) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $array = array($this->id, $this->name, $this->tag, $this->added, $this->updated, $this->active, $this->match);
        }
        Database::execute($sql,$array);
    }


}