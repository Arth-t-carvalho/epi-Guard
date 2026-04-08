<?php
require_once __DIR__ . '/src/Infrastructure/Database/Connection.php';
use Facchini\Infrastructure\Database\Connection;

try {
    $db = Connection::getInstance();
    
    // Check filial_id
    $res = $db->query("SHOW COLUMNS FROM usuarios LIKE 'filial_id'");
    $hasFilialId = ($res && $res->num_rows > 0);
    
    // Check preferencia_grafico
    $res = $db->query("SHOW COLUMNS FROM usuarios LIKE 'preferencia_grafico'");
    $hasPref = ($res && $res->num_rows > 0);
    
    echo "Has filial_id: " . ($hasFilialId ? "YES" : "NO") . "\n";
    echo "Has preferencia_grafico: " . ($hasPref ? "YES" : "NO") . "\n";
    
    $filialSelect = $hasFilialId ? "u.filial_id" : "1 as filial_id";
    $query = "SELECT u.id, $filialSelect FROM usuarios u LIMIT 1";
    echo "Query test: $query\n";
    
    $result = $db->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        print_r($row);
    } else {
        echo "Query failed: " . $db->error . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
