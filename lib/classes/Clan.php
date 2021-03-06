<?php


class Clan
{
    public static function updateClan($name) : Array
    {
        $return = array();

        $clanStats = GommeApi::fetchClanStats($name);

        // Überprüfen ob Clan existiert
        if ($clanStats == false) {
            array_push($return, "Clan existiert nicht");
            return $return;
        }

        $clan = self::getOrCreateClanByClanStats($clanStats);

        // Überprüfen ob Clan in bcs
        if (!$clan->isInBCs()) {
            array_push($return, "Clan existiert nicht in BCS");
            return $return;
        }

        $clan->save();
        array_push($return, "Clanname und Tag wurden aktualisiert");


        $clan->setActivePlayers();
        array_push($return, "Spielernamen und Aktivitätsstatus wurden aktualisiert");

        Scanner::scanExistingClan($clan);
        array_push($return, "Letzte Clan CWs wurden hinzugefügt");

        return $return;
    }

    /**
     * Reads Clan from DB or Creates a new one if no Clan exists
     *
     * @param String $name The name of the Clan
     * @return Clan Clan object
     */
    public static function getOrCreateClanByName($name) : Clan
    {
        $stats = GommeApi::fetchClanStats($name);
        if ($stats == null || $stats == false) {
            return new Clan("deleted","DeletedClan", "DEL", "0000-00-00 00:00:00", "0000-00-00 00:00:00", "0000-00-00 00:00:00", "no-matches", false);
        }
        return self::getOrCreateClanByClanStats(GommeApi::fetchClanStats($name));
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
        $stm = Database::execute($sql, array($cs['uuid']));

        $now = date('Y-m-d H:i:s');

        if ($stm->rowCount() == 1) {
            $data = $stm->fetchAll()[0];
            $added = $data['DateAdded'];
            $updated = $now;
            $active = $data['LastActive'];
            $match = $data['LastMatch'];
            $bcs = true;
        } else {
            $added = $now;
            $updated = $now;
            $active = "0000-00-00 00:00:00";
            $match = "notplayed";
            $bcs = false;
        }

        return new Clan($cs['uuid'], $cs['name'], $cs['tag'], $added, $updated, $active, $match, $bcs);
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
        return self::clanNameExists($name);
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
    public static function idInBCS(String $id)
    {
        $sql = "SELECT ClanName FROM clan WHERE ClanUUID = ?";
        $count = Database::count($sql, array($id));
        if ($count === 0) {
            return false;
        }
        return true;
    }

    /** Checks if a clan already Exists in BCS */
    public static function nameInBCS(String $name)
    {
        $sql = "SELECT ClanName, ClanUUID FROM clan WHERE ClanName = ?";
        $stm = Database::execute($sql, array($name));
        if ($stm->rowCount() === 0) {
            return false;
        }
        $data = $stm->fetchAll()[0];
        return $data['ClanUUID'];
    }

    private $id;
    private $name;

    private $tag;
    private $added;
    private $updated;
    private $active;
    private $match;

    private $bcs;

    private function __construct(String $id, String $name, String $tag, String $added, String $updated, String $active, String $match, Bool $bcs)
    {
        $this->id = $id;
        $this->name = $name;
        $this->tag = $tag;
        $this->added = $added;
        $this->updated = $updated;
        $this->active = $active;
        $this->match = $match;
        $this->bcs = $bcs;
    }

    public function isInBCs() : Bool
    {
        return $this->bcs;
    }

    public function id() : String
    {
        return $this->id;
    }

    public function name() : String
    {
        return $this->name;
    }

    public function lastMatchid() : String
    {
        return $this->match;
    }

    public function setLastMatch($matchid, $time)
    {
        $this->match = $matchid;
        $this->active = $time;
    }

    public function setActivePlayers()
    {
        $players = GommeApi::fetchClanMembers($this->name);

        $sql = "UPDATE member SET Active = 0 WHERE ClanUUID = ?";
        Database::execute($sql, array($this->id));

        if (!isset($players['leader'])) {
            $players['leader'] = array();
        }
        if (!isset($players['mods'])) {
            $players['mods'] = array();
        }
        if (!isset($players['member'])) {
            $players['member'] = array();
        }

        $all_player = array_merge($players['leader'], $players['mods'], $players['member']);
        $uuid_name_pairs = MojangApi::namesToUUID($all_player);

        $updateMember = "UPDATE member SET Active = 1 WHERE PlayerID = ?";
        $updatePlayer = "UPDATE player SET name = ? WHERE UUID = ?";
        foreach ($uuid_name_pairs as &$pair) {
            Database::execute($updateMember, array(md5($this->id . $pair['id'])));
            Database::execute($updatePlayer, array($pair['name'], $pair['id']));
        }

        $updatedLastRefreshed = "UPDATE clan SET DateUpdated = ? WHERE ClanUUID = ?";
        Database::execute($updatedLastRefreshed,[date('Y-m-d H:i:s'), $this->id]);
    }

    public function save()
    {
        $this->updated = date('Y-m-d H:i:s');
        $sql = "SELECT ClanName FROM clan WHERE ClanUUID = ?";
        $exists = Database::count($sql, array($this->id));

        if ($exists == 1) {
            $sql = "UPDATE clan SET ClanName = ?, ClanTag = ?, DateUpdated = ?, LastActive = ?, LastMatch = ? WHERE ClanUUID = ?";
            $array = array($this->name, $this->tag, $this->updated, $this->active, $this->match, $this->id);
        } else {
            $sql = "INSERT INTO clan (ClanUUID, ClanTag, ClanName, DateAdded, DateUpdated, LastActive, LastMatch) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $array = array($this->id, $this->name, $this->tag, $this->added, $this->updated, $this->active, $this->match);
        }
        Database::execute($sql,$array);
    }

    public function toEnemy() : Enemy
    {
        return Enemy::createEnemy($this->id, $this->tag, $this->name);
    }


}