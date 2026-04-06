<?php
/**
 * Migration: Add missing columns to ocorrencias table
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
    
    echo "Starting migration: Adding missing columns to 'ocorrencias' table...\n";

    // Add 'favorito' column
    echo "Adding 'favorito' column...\n";
    $pdo->exec("ALTER TABLE ocorrencias ADD COLUMN IF NOT EXISTS favorito BOOLEAN DEFAULT FALSE");

    // Add 'oculto' column
    echo "Adding 'oculto' column...\n";
    $pdo->exec("ALTER TABLE ocorrencias ADD COLUMN IF NOT EXISTS oculto BOOLEAN DEFAULT FALSE");

    // Add 'criado_em' column
    echo "Adding 'criado_em' column...\n";
    $pdo->exec("ALTER TABLE ocorrencias ADD COLUMN IF NOT EXISTS criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
