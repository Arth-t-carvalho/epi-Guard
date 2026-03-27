<?php
require_once __DIR__ . '/src/Infrastructure/Database/Connection.php';

use epiGuard\Infrastructure\Database\Connection;

$db = Connection::getInstance();
$result = $db->query("SELECT * FROM usuarios");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

print_r($users);
