<?php

if (!isset($_GET["SUPER-SECRET-KEY"])) {
    echo "Auth failed!";
    exit();
}

//TODO Validation

ignore_user_abort(true);

include_once "../../lib/autoload.php";

$memberRanker = new MemberRanker(10, true);
$memberRanker->createAndSave("../../data/tmp/ranking.json");