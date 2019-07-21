<?php

define("ROOT",substr(dirname(__FILE__),0, -4));

include_once ROOT . "/config/Config.php";
include_once ROOT . "/config/DBConfig.php";

spl_autoload_register(function ($class_name) {
    if (is_file(ROOT . "/lib/classes/" . $class_name . '.php')) {
        include ROOT . "/lib/classes/" . $class_name . '.php';
    }
});