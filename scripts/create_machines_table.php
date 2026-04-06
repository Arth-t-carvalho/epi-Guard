<?php
/**
 * Migration: Create maquinas table
 */

require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';

use epiGuard\Infrastructure\Database\Connection;

// Load environment variables manually from config/.env
if (file_exists(__DIR__ . '/../config/.env')) {
    $lines = file(__DIR__ . '/../config/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}

try {
    $pdo = Connection::getInstance();
    
    echo "Starting migration: Creating 'maquinas' table...\n";

    $sql = "
        CREATE TABLE IF NOT EXISTS maquinas (
            id SERIAL PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            setor_id INTEGER REFERENCES setores(id) ON DELETE CASCADE,
            epi_id INTEGER REFERENCES epis(id),
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    $pdo->exec($sql);

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
