<?php
session_start();

function showForm() {
    echo file_get_contents("login.html");
    exit();
}

require_once "../../lib/autoload.php";

if (!isset($_SESSION['logedin'])) {
    // Look if Login Process ongoing
    if (!isset($_POST['username']) and !isset($_POST['password'])) {
        // Send User login form
        showForm();
    } else {
        // Check if user exists
        $statement = Database::execute("SELECT password, rank FROM team WHERE name = ?", array($_POST['username']));

        if ($statement->rowCount() != 1) {
            showForm();
        }
        $data = $statement->fetch();

        // Check if password is right
        if (!password_verify($_POST['password'], $data['password'])) {
            showForm();
        }

        // Set Session
        $_SESSION['logedin'] = 1;
        $_SESSION['name'] = $_POST['username'];
        $_SESSION['rank'] = $data['rank'];
    }
}




?>
