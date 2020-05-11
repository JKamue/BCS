<?php

function applyClan($name)
{
    $limit = 10;

    $date = date('Y-m-d', strtotime(' +1 day'));

    $applications = Database::selectFirst("SELECT count(id) as 'amount' FROM `applications` WHERE `done` != 1 AND `added` = ?",[$date]);

    if ($applications["amount"] >= 5) {
        return "Es wurden heute schon $limit Clans registriert!";
    }

    if (!Clan::clanNameExists($name)) {
        return "Clan '$name' existiert nicht auf gommehd.net!";
    }

    $id = GommeApi::convertNameToUUID($name);

    if (Clan::idInBCS($id)) {
        return "Dieser Clan ist bereits in BCS, suche ihn einfach!";
    }

    $entries = Database::selectFirst("SELECT count(`id`) as 'amount' FROM `applications` WHERE `id`= ? and `done` = 0 and `added` = ?",[$id, $date]);

    if ($entries["amount"] > 0) {
        return "Für diesen Clan wurde bereits eine Anlegung beantragt!";
    }

    Database::execute("INSERT INTO `applications` (`id`, `added`, `done`) VALUES (?, ?, ?);",[$id, $date, 0]);

    return "Clan wird zwischen 04:00 und 07:00 automatisch angelegt!";
}