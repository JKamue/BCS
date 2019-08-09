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

        foreach ($games as &$game) {
            $i++;
            if ($debug) {self::progressBar($i, 100, $game['matchid']);}
            $cw = Cw::getMatch($game, $clans);
            $cw->compute();
            $cw->save();
        }

        $clan->setActivePlayers();
    }

    function progressBar($done, $total, $matchid) {
        $perc = floor(($done / $total) * 100);
        $write = sprintf("$perc%% - $done/$total currently $matchid" . PHP_EOL, "", "");
        fwrite(STDERR, $write);
    }
}