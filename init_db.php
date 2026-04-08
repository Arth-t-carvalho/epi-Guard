<?php
/**
 * Script para inicializar o banco de dados via PDO/PostgreSQL
 * Cria o banco se não existir e executa o esquema
 */

$host = '127.0.0.1';
$user = 'postgres';
$pass = 'F@cchini2025';
$dbName = 'epi_guard';
$port = '5432';

try {
    // 1. Conectar ao PostgreSQL (banco padrão 'postgres' para criar o novo)
    $dsn = "pgsql:host=$host;port=$port;dbname=postgres;sslmode=disable";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Conectado ao PostgreSQL.\n";

    // 2. Verificar se o banco existe
    $stmt = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
    $stmt->execute([$dbName]);
    
    if (!$stmt->fetch()) {
        $pdo->exec("CREATE DATABASE \"$dbName\"");
        echo "Banco de dados `$dbName` criado.\n";
    } else {
        echo "Banco de dados `$dbName` já existe.\n";
    }

    // 3. Conectar ao banco específico
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbName;sslmode=disable";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // 4. Ler e executar o schema.sql
    // Nota: O schema.sql deve ser compatível com PostgreSQL
    $sqlFilePath = __DIR__ . '/database/schema.sql';
    if (file_exists($sqlFilePath)) {
        $sql = file_get_contents($sqlFilePath);
        $pdo->exec($sql);
        echo "Esquema executado com sucesso no banco `$dbName`!\n";
    } else {
        echo "AVISO: Arquivo schema.sql não encontrado em $sqlFilePath\n";
    }

} catch (PDOException $e) {
    echo "ERRO CRÍTICO (PostgreSQL): " . $e->getMessage() . "\n";
}
