<?php

include_once "../../lib/autoload.php";
include ROOT . "/src/team/api.php";

/** @var Session $Session */
if(!$Session->isLogedIn()) {
    exit;
}

if (isset($_GET['addClan'])) {
    ignore_user_abort(true);
    ini_set('max_execution_time', 600);
    try {
        Scanner::scanClan($_GET['addClan'], true);
    } catch (Exception $e) {
        http_response_code(500);
        echo $e->getMessage();
    }
} else if (isset($_GET['showClans'])) {
    echo json_encode(listClans());
} else if (isset($_GET['BCSInformation'])) {
    echo json_encode(getStats());
} else if (isset($_GET['getSupports'])) {
    echo json_encode(getSupport());
} else if (isset($_GET['changeStatus'])) {
    changeStatus($_GET['changeStatus'], $_GET['magnitude']);
    echo json_encode("done");
} else if (isset($_GET['setDone'])) {
    setDone($_GET['setDone']);
    echo json_encode("done");
} else if (isset($_GET['team']) && $_SESSION['rank'] == 1) {
    echo json_encode(getTeam());
} else if (isset($_GET['addTeam']) && $_SESSION['rank'] == 1) {
    echo newTeam($_GET['addTeam']);
} else if (isset($_GET['deleteTeam']) && $_SESSION['rank'] == 1) {
    echo deleteTeam($_GET['deleteTeam']);
} else if (isset($_GET['setRank']) && $_SESSION['rank'] == 1) {
    echo setRank($_GET['setRank'],$_GET['name']);
} else if (isset($_GET['delClan']) && $_SESSION['rank'] == 1) {
    echo json_encode(delClan($_GET['delClan']));
} else if (isset($_GET['checkClan'])) {
    ignore_user_abort(true);
    echo json_encode(Clan::updateClan($_GET['checkClan']));
}
