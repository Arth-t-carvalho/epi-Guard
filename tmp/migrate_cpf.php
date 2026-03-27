<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
use epiGuard\Infrastructure\Database\Connection;

try {
    $db = Connection::getInstance();
    
    // Check if cpf column already exists in funcionarios
    $result = $db->query("SHOW COLUMNS FROM funcionarios LIKE 'cpf'");
    if ($result && $result->num_rows === 0) {
        echo "Adding 'cpf' column to 'funcionarios'...\n";
        $db->query("ALTER TABLE funcionarios ADD COLUMN cpf VARCHAR(14) AFTER nome");
        echo "Successfully added 'cpf' column to 'funcionarios'!\n";
    } else {
        echo "'cpf' column already exists in 'funcionarios'.\n";
    }

    // Check if cpf column already exists in usuarios
    $resultUser = $db->query("SHOW COLUMNS FROM usuarios LIKE 'cpf'");
    if ($resultUser && $resultUser->num_rows === 0) {
        echo "Adding 'cpf' column to 'usuarios'...\n";
        $db->query("ALTER TABLE usuarios ADD COLUMN cpf VARCHAR(14) AFTER nome");
        echo "Successfully added 'cpf' column to 'usuarios'!\n";
    } else {
        echo "'cpf' column already exists in 'usuarios'.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
