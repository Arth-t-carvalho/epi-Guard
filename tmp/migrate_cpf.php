<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
try {
    $db = \epiGuard\Infrastructure\Database\Connection::getInstance();
    
    // Testa se a coluna já existe
    $check = $db->query("SHOW COLUMNS FROM funcionarios LIKE 'cpf'");
    if ($check->num_rows == 0) {
        $db->query("ALTER TABLE funcionarios ADD COLUMN cpf VARCHAR(14) AFTER nome");
        echo "Coluna 'cpf' adicionada com sucesso.\n";
        
        // Adiciona alguns CPFs fictícios para teste
        $db->query("UPDATE funcionarios SET cpf = CONCAT(LPAD(FLOOR(RAND()*999), 3, '0'), '.', LPAD(FLOOR(RAND()*999), 3, '0'), '.', LPAD(FLOOR(RAND()*999), 3, '0'), '-', LPAD(FLOOR(RAND()*99), 2, '0'))");
        echo "CPFs fictícios gerados.\n";
    } else {
        echo "Coluna 'cpf' já existe.\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
