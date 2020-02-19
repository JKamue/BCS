<?php
include_once "../../lib/autoload.php";
include ROOT . "/src/analytics/api.php";

$data = createNodesAndEdges();

echo "var nodes = [\n";
foreach ($data["nodes"] as $name => $number) {
    if($number < $data["clan_amount"]) {
        // Clan
        echo "\t{id: {$number}, label: '{$name}', x: 0, y: 0, shape: 'dot' },\n";
    } else {
        // Player
        echo "\t{id: {$number}, label: '{$name}', x: 0, y: 0, shape: 'circle'},\n";
    }
}
echo "];\n\n";

echo "var edges = [\n";
foreach ($data["edges"] as &$reference) {
    echo "\t{from: {$reference[0]}, to: {$reference[1]}, value:{$reference[2]}},\n";
}
echo "];";
