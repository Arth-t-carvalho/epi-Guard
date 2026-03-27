<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
use epiGuard\Infrastructure\Database\Connection;

$db = Connection::getInstance();

// Tenta adicionar a coluna 'lida' se ela não existir
try {
    $db->query("ALTER TABLE ocorrencias ADD COLUMN lida TINYINT(1) DEFAULT 0 AFTER tipo");
    echo "Coluna 'lida' adicionada com sucesso!\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "A coluna 'lida' já existe.\n";
    } else {
        echo "Erro ao adicionar coluna: " . $e->getMessage() . "\n";
    }
}
?>
