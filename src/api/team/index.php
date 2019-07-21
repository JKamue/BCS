<?php

include_once "../../../lib/autoload.php";

if (isset($_GET['addClan'])) {
    include ROOT . "/src/team/clans.php";
    $response = addClan($_GET['addClan']);

    var_dump($response);
}
