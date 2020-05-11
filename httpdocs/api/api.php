<?php
include_once "../../lib/autoload.php";
include ROOT . "/src/bcs/api.php";

if (isset($_GET['clanname'])) {
    $name = $_GET['clanname'];
    $inBcs = Clan::nameInBCS($name);
    if (!$inBcs) {
        echo json_encode(array("error" => true, "mes" => "Clan not in BCS"));
        http_response_code(404);
        exit();
    }

    if (isset($_GET["type"])) {
        $type = $_GET["type"];
        if ($type == "clan") {
            echo json_encode(getClanStats($inBcs)[0]);
            exit();
        } elseif ($type == "member") {
            echo json_encode(getMemberStats($inBcs));
            exit();
        } elseif ($type == "map") {
            echo json_encode(getMapStats($inBcs));
            exit();
        } elseif ($type == "lineup") {
            echo json_encode(getLineupStats($inBcs));
            exit();
        } elseif ($type == "enemy") {
            echo json_encode(getEnemyStats($inBcs));
            exit();
        }
    }
    echo json_encode(getAllClanData($inBcs));

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
} elseif (isset($_GET['player'])) {
    $names = playerNameToUUID($_GET['player']);
    if(count($names) == 0) {
        echo json_encode(array("error" => true, "mes" => "Player not in BCS"));
        http_response_code(404);
        exit();
    }
    echo json_encode(getPlayerStats($names[0]["UUID"]));
} elseif (isset($_GET['apply'])) {
    include ROOT . "/src/apply/apply.php";
    $clanname = $_GET["apply"];
    echo applyClan($clanname);
}
