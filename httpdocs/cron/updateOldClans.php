<?php

if (!isset($_GET["SUPER-SECRET-KEY"])) {
    echo "Auth failed!";
    exit();
}

ignore_user_abort(true);

include_once "../../lib/autoload.php";

Updater::updateInactiveClans(3);
?>