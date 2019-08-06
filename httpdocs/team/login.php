<?php

function showForm() {
    echo file_get_contents("login.html");
    exit();
}

require_once "../../lib/autoload.php";

if (!$Session->isLogedIn()) {
    // Look if Login Process ongoing
    if (!isset($_POST['username']) and !isset($_POST['password'])) {
        // Send User login form
        showForm();
    } else {
        // Check if user exists
        $pdo = Database::getConnection("main");
        $statement = $pdo->prepare("SELECT password, rank FROM team WHERE name = ?");
        $statement->execute(array($_POST['username']));

        if ($statement->rowCount() != 1) {
            showForm();
        }
        $data = $statement->fetch();

        // Check if password is right
        if (!password_verify($_POST['password'], $data['password'])) {
            showForm();
        }

        // Set Session
        $Session->login($_POST['username'], $data['rank']);
    }
}




?>
