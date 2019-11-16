<?php
include_once "../../lib/autoload.php";
include ROOT . "/src/bcs/api.php";

if (isset($_GET['clanname'])) {
    $name = $_GET['clanname'];
    $inBcs = Clan::nameInBCS($name);
    if ($inBcs != false) {
        echo json_encode(getAllClanData($inBcs));
    } else {
        echo json_encode(array("error" => true, "mes" => "Clan not in BCS"));
        http_response_code(404);
    }
} elseif (isset($_GET['bcsstats'])) {
    echo json_encode(getBCSStats());
} elseif (isset($_GET['search'])) {
    $result['clans'] = bcsSearchClan($_GET['search']);
    $result['member'] = bcsSearchPlayer($_GET['search']);
    echo json_encode($result);
} elseif (isset($_GET['member'])) {
    echo json_encode(getPlayerStats($_GET['member']));
} elseif (isset($_GET['getAllClans'])) {
    echo json_encode(getAllClanstats());
} elseif (isset($_GET['getRanking'])) {
    echo json_encode(getRanking(10));
}
