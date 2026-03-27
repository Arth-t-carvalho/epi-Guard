<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
$db = \epiGuard\Infrastructure\Database\Connection::getInstance();
$result = $db->query("DESCRIBE ocorrencias");
while ($row = $result->fetch_assoc()) {
    echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
}
echo "---\n";
$result = $db->query("DESCRIBE setores");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
    }
} else {
    echo "setores table not found\n";
}
