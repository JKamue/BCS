<?php


class Updater
{
    public static function updateInactiveClans($maximum = 20) {
        $date = new DateTime();
        date_sub($date, date_interval_create_from_date_string('2 days'));
        $formatted = $date->format('Y-m-d H:i:s');

        $fetch_incative_clans = "SELECT ClanName FROM clan WHERE DateUpdated < ?";

        $clans = Database::select($fetch_incative_clans, array($formatted));

        for ($i = 0; $i < $maximum; $i++) {
            $clan = Clan::getOrCreateClanByName($clans[$i][0]);
            if ($clan->id() == "deleted") {
                $maximum += 1;
                echo $clans[$i][0] . " was deleted<br>\n";
            } else {
                $clan->setActivePlayers();
                echo "updated " . $clans[$i][0] . "<br>\n";
            }
        }
    }
}