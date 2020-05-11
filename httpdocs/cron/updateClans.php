<?php

if (!isset($_GET["SUPER-SECRET-KEY"])) {
    echo "Auth failed!";
    exit();
}

ignore_user_abort(true);
set_time_limit(120);

include_once "../../lib/autoload.php";

Scanner::scanLatestGames();