<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
use Facchini\Infrastructure\Database\Connection;

try {
    $db = Connection::getInstance();
    $stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'ocorrencias'");
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
