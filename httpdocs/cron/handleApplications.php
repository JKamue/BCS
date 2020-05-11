<?php

ignore_user_abort(true);
set_time_limit(6 * 60);
ob_start();

include_once "../../lib/autoload.php";

$application_query = "SELECT id FROM `applications` WHERE `added` = CURRENT_DATE() AND `done` = 0";
$delete_query = "DELETE FROM `applications` WHERE `id` = ?";
$update_query = "UPDATE `applications` SET `done`= 1 WHERE `id`= ?";

$data = Database::select($application_query,[]);
if (!isset($data[0])) {
    echo "Nothing to do";
    exit();
}

$id = $data[0]["id"];

if (GommeApi::convertUUIDtoName($id) == null) {
    echo "Clan was deleted!";
    Database::execute($delete_query, [$id]);
    exit;
}

if (Clan::idInBCS($id)) {
    echo "Clan was already added!";
    Database::execute($delete_query, [$id]);
    exit;
}


$name = GommeApi::convertUUIDtoName($id);

echo "Adding $name $id";

header('Connection: close');
header('Content-Length: '.ob_get_length());
ob_end_flush();
ob_flush();
flush();

sleep(10);

try {
    Scanner::scanClan($name, false);
} catch (Exception $e) {
    echo "fuck";
}

Database::execute($update_query, [$id]);
echo "done";