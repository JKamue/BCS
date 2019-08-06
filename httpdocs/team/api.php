<?php
//TODO LOGIN
include_once "../../lib/autoload.php";
include ROOT . "/src/team/api.php";

if (isset($_GET['addClan'])) {
    ignore_user_abort(true);
    $response = addClan($_GET['addClan']);
    http_response_code($response['code']);
    echo $response['mes'];
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
} else if (isset($_GET['team'])) {
    echo json_encode(getTeam());
} else if (isset($_GET['addTeam'])) {
    echo newTeam($_GET['addTeam']);
} else if (isset($_GET['deleteTeam'])) {
    echo deleteTeam($_GET['deleteTeam']);
} else if (isset($_GET['setRank'])) {
    echo setRank($_GET['setRank'],$_GET['name']);
} else if (isset($_GET['delClan'])) {
    echo json_encode(delClan($_GET['delClan']));
}
