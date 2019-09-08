<?php


class Scanner
{
    public static function scanClan($clanname, $debug = false)
    {
        set_time_limit(500);
        $clan = Clan::getOrCreateClanByName($clanname);
        $clan->save();
        $clan = Clan::getOrCreateClanByName($clanname);
        $games = GommeApi::fetchClanCws($clan->id(),100, "bedwars", "true");
        $clans = array($clan);
        $i = 0;


        $games = array_reverse($games);
        foreach ($games as &$game) {
            try {
                $i++;
                if ($debug) {self::progressBar($i, 100, $game['matchid']);}
                $cw = Cw::getMatch($game, $clans);
                $cw->compute();
                $cw->save();
            } catch (Exception $e) {
                echo '<b>Fehler ist aufgetreten: ',  $e->getMessage(), "\n</b><br>";
            }
        }

        $clan->setActivePlayers();
    }

    public static function scanExistingClan(Clan $clan)
    {
        set_time_limit(500);
        $games = GommeApi::fetchClanCws($clan->id(),100, "bedwars", "true");
        $clans = array($clan);
        $i = 0;

        $lastMatchid = $clan->lastMatchid();

        $games = array_reverse($games);
        foreach ($games as &$game) {

            $i++;
            $cw = Cw::getMatch($game, $clans);
            $cw->compute();
            $cw->save();
        }

        $clan->setActivePlayers();
    }

    public static function scanLatestGames($debug = false)
    {
        set_time_limit(500);
        $games = GommeApi::fetchLastCws(100);
        $i = 0;

        $firstGame = $games[0]['matchid'];
        $lastGame = file_get_contents(ROOT . "/data/tmp/lastscanned.txt");

        foreach ($games as &$game) {
            $i++;

            if ($game['matchid'] == $lastGame) {
                break;
            }


            if ($debug) {self::progressBar($i, 100, $game['matchid']);}
            $cw = Cw::getMatch($game);
            $cw->compute();
            $cw->save();
            echo PHP_EOL;
        }

        file_put_contents(ROOT . "/data/tmp/lastscanned.txt", $firstGame);
    }

    public static function progressBar($done, $total, $matchid) {
        if (php_sapi_name() !== 'cli') {
            echo "currently $matchid<br>\n";
            flush();
        } else {
            $perc = floor(($done / $total) * 100);
            $write = sprintf("$perc%% - $done/$total currently $matchid" . PHP_EOL, "", "");
            fwrite(STDERR, $write);
        }
    }
}