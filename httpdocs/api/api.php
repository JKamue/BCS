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
}
