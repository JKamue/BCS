<?php


class Clan
{
    /**
     * Checks if a Clan is already in the system
     *
     * @param $uuid The id of the clan
     * @param $pdo Database instance
     * @return int The amount of Clans with that uuid
     */
    public static function exists($uuid, $pdo) : Int {
        $statement = $pdo->prepare("SELECT Count(clan.ClanUUID) AS Amount From clan Where ClanUUID = ?");
        $statement->execute(array($uuid));
        return $statement->fetch()['Amount'];
    }

    /**
     * Returns the members of a clan as uuids
     *
     * @param $name The playername
     * @return bool|mixed|string An list with an array with name and id of each player
     */
    public static function getMemberUUIDs($name) {
        // Generate a list of all Players
        $members = GommeApi::fetchClanMembers($name);
        $ranks = array("leader", "mods", "member");
        $names = array();
        for($i = 0; $i < 3; $i++) {
            for($a = 0; $a < sizeof($members[$ranks[$i]]); $a++) {
                array_push($names, $members[$ranks[$i]][$a]);
            }
        }

        // Convert the list to UUIDS
        return MojangApi::namesToUUID($names);
    }

    /**
     * Saves the last scanned cw for a clan
     *
     * @param $cw The CW data
     * @param $uuid The Clan UUID
     */
    public static function setLastCw($cw, $uuid) {
        $date=date_create($cw['datetime']);
        fwrite(fopen(ROOT . "/data/tmp/lastcw/" . $uuid . ".txt","w+"),$cw['matchid'] . "+" . date_format($date,"Y-m-d H:i:s"));
    }

    /**
     * Gets the last saved CW of a clan
     *
     * @param $uuid The clan UUID
     * @return array where 0 is the maatchid and 1 is the time of the CW
     */
    public static function checkLastCw($uuid) {
        return explode("+", file_get_contents(ROOT . "/data/tmp/lastcw/" . $uuid . ".txt"));
    }


    /**
     * Checks if a cw is already scanned in
     *
     * @param $cw The cw data
     * @param $uuid The clan uuid
     * @return bool Is an old cw or not
     */
    public static function isOldCw($cw, $uuid) {
        // TODO use hash of matchid and clan to compute gameid
        $last_cw = self::checkLastCw($uuid);
        $date=date_create($cw['datetime']);
        if ($last_cw[0] == $cw['matchid'] or $last_cw[1] > date_format($date,"Y-m-d H:i:s")) {
            return true;
        } else {
            return false;
        }
    }

    public static function addCw($cw, $pdo, $clan1=array("name" => "null"), $clan2=array("name" => "null")) {
        $type = array("winner","loser");
        $scan = false;
        $clandata = array();

        // Check if any of the clans is in bcs
        // Dont have to scan the cw if none of them are
        for ($i = 0; $i < 2; $i++) {
            // We first need the clan uuid to check if the clan is in BCS

            $clan = $cw[$type[$i]];

            if ($clan == $clan1['name']) {
                // Data already in parameters
                $clandata[$i] = $clan1;
            } elseif ($clan == $clan2['name']) {
                // Data already in parameters
                $clandata[$i] = $clan2;
            } else {
                // Get data over API
                if (GommeApi::fetchClanStats($clan) == false) {
                    $clandata[$i]['name'] = "deleted";
                    $clandata[$i]['tag'] = "del";
                    $clandata[$i]['uuid'] = "deleted";
                } else {
                    $clandata[$i] = GommeApi::fetchClanStats($clan);
                }


            }

            // Check if the clan is part of BCS
            if (self::exists($clandata[$i]['uuid'], $pdo)) {
                // Check if the CW is an old CW for that clan
                if (!self::isOldCw($cw, $clandata[$i]['uuid'])) {
                    $scan = true;
                } else {
                    echo "jabadabaduuu";
                }
            }
        }

        // If at least one of the clans is in bcs and wants the result of the cw
        if ($scan == true) {
            // Receive all data
            $data = array();
            $data['stats'] = GommeApi::fetchCwStats($cw['matchid']);
            $data['player'] = GommeApi::fetchCwPlayers($cw['matchid']);
            $data['actions'] = GommeApi::fetchCwActions($cw['matchid']);

            // Convert add uuids to all players
            $players = array();

            for ($a = 0; $a < 2; $a++) {
                for ($b = 0; $b < 4; $b++) {
                    array_push($players, $data['player'][$type[$a]]['lineup'][$b]['name']);
                }
            }

            $uuids = MojangApi::namesToUUID($players);
            for ($a = 0; $a < 2; $a++) {
                for ($b = 0; $b < 4; $b++) {
                    // In case there is an error and no uuid is found we still need a unique id so 0 is not good enough
                    $player = uniqid();
                    $name = $data['player'][$type[$a]]['lineup'][$b]['name'];

                    // Look where the uuid is
                    foreach ($uuids as &$uuid) {
                        if($name == $uuid['name']) {
                            $player = $uuid['id'];
                        }
                    }

                    // Connect uuid to name
                    $data['player'][$type[$a]]['lineup'][$b]['uuid'] = $player;
                }
            }


            // Add CW to clan
            for ($i = 0; $i < 2; $i++) {
                if (self::exists($clandata[$i]['uuid'], $pdo)) {
                    if (!Clan::isOldCw($cw, $clandata[$i]['uuid'])) {
                        // Look if we have the enemy data as well, otherwise we will need the UUID over the API
                        if ($i == 0) {
                            $enemy = 1;
                        } else {
                            $enemy = 0;
                        }

                        if (isset($clandata[$enemy])) {
                            $data['enemy'] = $clandata[$enemy];
                        } else {
                            $data['enemy'] = GommeApi::fetchClanStats($cw[$type[$enemy]]);
                        }


                        $data['clan'] = $clandata[$i];
                        self::addCwToClan($cw, $pdo, $data);
                    }
                }
            }
        }
    }

    public static function addCwToClan($cw, $pdo, $data) {
        //echo json_encode($data);

        // Gegner als enemy anlegen
        $enemy = $pdo->prepare("INSERT IGNORE INTO enemy(EnemyUUID, ClanTag, ClanName)
            VALUES( ?, ?, ?);");
        $enemy->execute(array($data['enemy']['uuid'], $data['enemy']['tag'], $data['enemy']['name']));

        // TODO change enemy name if it changed

        // Map
        // Schauen ob map existiert
        $statement = $pdo->prepare("SELECT Count(map.MapName) AS Amount From map Where MapName = ?");
        $statement->execute(array($cw['map']));
        if ($statement->fetch()['Amount'] == 0) {
            // Add map
            $num = file_get_contents(ROOT . "/data/tmp/mapcounter.txt");

            $map = $pdo->prepare("INSERT IGNORE INTO map(MapID, MapName)
                VALUES(?, ?);");
            $map->execute(array($num, $cw['map']));

            $mapid = $num;
            $num++;
            file_put_contents(ROOT . "/data/tmp/mapcounter.txt", $num);
        } else {
            // Get Map ID
            $statement = $pdo->prepare("SELECT map.MapID From map Where MapName = ?");
            $statement->execute(array($cw['map']));
            $mapid = $statement->fetch()['MapID'];
        }

        // Check if clan is winner or loser
        if ($data['stats']['winner']['name'] == $data['clan']['name']) {
            $status = "winner";
            $win = true;
        } else {
            $status = "loser";
            $win = false;
        }

        // Lineup hinzufügen
        $lineup = $data['player'][$status]['lineup'];

        $players = array($lineup[0]['uuid'], $lineup[1]['uuid'], $lineup[2]['uuid'], $lineup[3]['uuid']);
        sort($players);
        $lineupid = md5($players[0] . $players[1] . $players[2] . $players[3]);

        $newlineup = $pdo->prepare("INSERT INTO lineup(LineupID, ClanUUID, Player1UUID, Player2UUID, Player3UUID, Player4UUID)
            VALUES(?, ?, ?, ?, ?, ?);");
        $clanuuid = $data['clan']['uuid'];
        $newlineup->execute(array($lineupid, $data['clan']['uuid'],
            md5($clanuuid.$players[0]),
            md5($clanuuid.$players[1]),
            md5($clanuuid.$players[2]),
            md5($clanuuid.$players[3])));

        // Check if the game was a BAC game
        $bac = 1;
        for ($i = 0; $i < 4; $i++) {
            if ($lineup[$i]['bac'] == false) {
                $bac = 0;
            }
        }

        // game anlegen
        $id = uniqid();
        $newlineup = $pdo->prepare("INSERT INTO game(GameID, Win, Elo, GameTime,BACGame, MapID, LineupID, EnemyUUID, ClanUUID, MatchID)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
        $newlineup->execute(array($id, $win, $data['stats']['elo'], ceil($cw['duration']), $bac, $mapid, $lineupid, $data['enemy']['uuid'], $data['clan']['uuid'], $cw['matchid']));

        // alle 4 spieler anlegen / updaten

        $playerstats = array();
        for ($i = 0; $i < 4; $i++) {

            $player = $lineup[$i];
            $playerstats[$player['name']] = $player;

            if ($player['name'] == $data['stats']['mvp']) {
                $playerstats[$player['name']]['mvp'] = 1;
            } else {
                $playerstats[$player['name']]['mvp'] = 0;
            }

            $playerstats[$player['name']]['beds'] = 0;
            $playerstats[$player['name']]['kills'] = 0;
            $playerstats[$player['name']]['killed'] = 0;
            $playerstats[$player['name']]['quits'] = 0;
            $playerstats[$player['name']]['died'] = 0;
            $playerstats[$player['name']]['bac'] = $bac;

            // Add as player if not there already
            $stm = $pdo->prepare("INSERT IGNORE INTO player (UUID, name)
	            VALUES (?, ?);");
            $stm->execute(array($player['uuid'], $player['name']));

            // Add as member if not there already
            $statement = $pdo->prepare("SELECT Count(member.playerID) AS Amount From member Where ClanUUID = ? AND UUID = ?");
            $statement->execute(array($data['clan']['uuid'], $player['uuid']));
            if ($statement->fetch()['Amount'] == 0) {
                // Add member
                $memberdb = $pdo->prepare("Insert IGNORE INTO member (PlayerID, Active, MVP, Betten, Kills, Killed, Quits, Died, BAC, ClanUUID, UUID)
	            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $memberdb->execute(array(uniqid(), 1, 0, 0, 0, 0, 0, 0, 0, $data['clan']['uuid'], $player['uuid']));
            }

        }

        // stats für jeden Spieler berechnen
        foreach ($data['actions'] as &$action) {
            if ($action['action'] == "quit") {
                if (isset($playerstats[$action['subject']])) {
                    $playerstats[$action['subject']]['quits'] += 1;
                }
            } elseif ($action['action'] == "joined") {
                if (isset($playerstats[$action['subject']])) {
                    $playerstats[$action['subject']]['quits'] -= 1;
                }
            } elseif ($action['action'] == "destroyed") {
                if (isset($playerstats[$action['subject']])) {
                    $playerstats[$action['subject']]['beds'] += 1;
                }
            } elseif ($action['action'] == "killed") {
                if (isset($playerstats[$action['subject']])) {
                    $playerstats[$action['subject']]['kills'] += 1;
                }
                if (isset($playerstats[$action['object']])) {
                    $playerstats[$action['object']]['killed'] += 1;
                }
            } elseif ($action['action'] == "died") {
                if (isset($playerstats[$action['subject']])) {
                    $playerstats[$action['subject']]['died'] += 1;
                }
            }
        }

        foreach ($playerstats as &$player) {
            $memberdb = $pdo->prepare(" UPDATE member SET `MVP` = `MVP` + ?,
                `Betten` = `Betten` + ?,
                `Kills` = `Kills` + ?,
                `Killed` = `Killed` + ?,
                `Quits` = `Quits` + ?,
                `Died` = `Died` + ?,
                `BAC` = `BAC` + ?
             Where `ClanUUID` = ? AND `UUID` = ?");
            $memberdb->execute(array($player['mvp'], $player['beds'], $player['kills'], $player['killed'], $player['quits'], $player['died'], $bac, $data['clan']['uuid'], $player['uuid']));
        }


        // Check if Clanname changed
        // Todo
        // Update clan last updated etc
        self::setLastCw($cw,$data['clan']['uuid']);
    }
}