<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
$db = \epiGuard\Infrastructure\Database\Connection::getInstance();
foreach (['ocorrencia_epis', 'epis'] as $table) {
    echo "-- $table --\n";
    $res = $db->query("DESCRIBE $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo $row['Field'] . ' | ' . $row['Type'] . "\n";
        }
    } else {
        echo "$table not found\n";
    }
}
