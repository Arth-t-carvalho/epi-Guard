<?php
require_once __DIR__ . '/src/Infrastructure/Database/Connection.php';
use Facchini\Infrastructure\Database\Connection;

try {
    $pdo = Connection::getInstance();
    
    echo "--- SETORES ---\n";
    $stmt = $pdo->query("SELECT id, nome FROM setores");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\n--- FUNCIONARIOS ---\n";
    $stmt = $pdo->query("SELECT id, nome FROM funcionarios LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\n--- OCORRENCIAS ---\n";
    $stmt = $pdo->query("SELECT id, data_hora FROM ocorrencias LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage();
}
