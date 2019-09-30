<?php

include_once "../../lib/autoload.php";

if (isset($_GET['function'])) {
    $function = $_GET['function'];

    if ($function == "toName") {
        if (!isset($_GET['uuid'])) {
            $response = "Error: No UUID defined";
        } else {
            $response = GommeApi::convertUUIDtoName($_GET['uuid']);
        }
    } elseif ($function == "toUUID") {
        if (!isset($_GET['name'])) {
            $response = "Error: No Name defined";
        } else {
            $response = GommeApi::convertNameToUUID($_GET['name']);
        }
    } elseif ($function == "clanStats") {
        if (!isset($_GET['name'])) {
            $response = "Error: No Name defined";
        } else {
            $response = GommeApi::fetchClanStats($_GET['name']);
        }
    } elseif ($function == "clanMember"){
        if (!isset($_GET['name'])) {
            $response = "Error: No Name defined";
        } else {
            $response = GommeApi::fetchClanMembers($_GET['name']);
        }
    } elseif ($function == "clanHistory") {
        if (!isset($_GET['name'])) {
            $response = "Error: No Name defined";
        } else {
            $response = GommeApi::fetchClanHistory($_GET['name']);
        }
    } elseif ($function == "clanCws") {
        if (!isset($_GET['uuid']) or !isset($_GET['amount'])) {
            $response = "Error: NOt all Params defined";
        } else {
            if (isset($_GET['finished']) && isset($_GET['game'])) {
                $response = GommeApi::fetchClanCws($_GET['uuid'],$_GET['amount'],$_GET['game'],$_GET['finished']);
            } else {
                $response = GommeApi::fetchClanCws($_GET['uuid'],$_GET['amount']);
            }
        }
    } elseif ($function == "cwStats") {
        if (!isset($_GET['uuid'])) {
            $response = "Error: No CW ID defined";
        } else {
            $response = GommeApi::fetchCwStats($_GET['uuid']);
        }
    } elseif ($function == "cwPlayers") {
        if (!isset($_GET['uuid'])) {
            $response = "Error: No CW ID defined";
        } else {
            $response = GommeApi::fetchCwPlayers($_GET['uuid']);
        }
    } elseif ($function == "cwActions") {
        if (!isset($_GET['uuid'])) {
            $response = "Error: No CW ID defined";
        } else {
            $response = GommeApi::fetchCwActions($_GET['uuid']);
        }
    } else {
        $response = "Error: unknown function $function";
    }

    echo json_encode($response);
} else {
    echo json_encode("Error: No function defined");
}


?>