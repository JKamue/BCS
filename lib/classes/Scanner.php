<?php


class Scanner
{
    public static function scanClan($clanname, $debug = false)
    {
        $clan = Clan::getOrCreateClanByName($clanname);
        $clan->save();
        $clan = Clan::getOrCreateClanByName($clanname);
        $games = GommeApi::fetchClanCws($clan->id(),100, "bedwars", "true");
        $clans = array($clan);
        $i = 0;

        $games = array_reverse($games);
        foreach ($games as &$game) {
            $i++;
            if ($debug) {self::progressBar($i, 100, $game['matchid']);}
            $cw = Cw::getMatch($game, $clans);
            $cw->compute($debug);
            $cw->save();
        }

        $clan->setActivePlayers();
    }

    public static function scanLatestGames($debug = false) {
        $games = GommeApi::fetchLastCws(100);
        $i = 0;

        $games = array_reverse($games);
        foreach ($games as &$game) {
            $i++;
            if ($debug) {self::progressBar($i, 100, $game['matchid']);}
            $cw = Cw::getMatch($game);
            $cw->compute($debug);
            $cw->save();
            echo PHP_EOL;
        }
    }

    public static function progressBar($done, $total, $matchid) {
        if (php_sapi_name() !== 'cli') {
            echo "currently $matchid<br>\n";
            flush();
        } else {
            $perc = floor(($done / $total) * 100);
            $write = sprintf("$perc%% - $done/$total currently $matchid", "", "");
            fwrite(STDERR, $write);
        }
    }
}