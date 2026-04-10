<?php
require 'src/Infrastructure/Database/Connection.php';
try {
    $db = \Facchini\Infrastructure\Database\Connection::getInstance();
    $db->exec("ALTER TABLE filiais ADD COLUMN IF NOT EXISTS cor_grafico_total VARCHAR(10) DEFAULT '#334155';");
    echo "Coluna adicionada com sucesso!";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "A coluna já existe.";
    } else {
        echo "Erro: " . $e->getMessage();
    }
}
