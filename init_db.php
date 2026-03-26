<?php
/**
 * Script para inicializar o banco de dados via MySQLi
 * Cria o banco se não existir e executa o esquema
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = '127.0.0.1'; // Trocando localhost por IP direto
$user = 'root';
$pass = ''; // Senha resetada para vazia conforme teste de sucesso
$dbName = 'epi_guard';

try {
    // 1. Conectar ao MySQL
    $port = 3308;
    $mysqli = new mysqli($host, $user, $pass, null, $port);
    echo "Conectado ao MySQL.\n";

    // 2. Criar o banco de dados
    $sqlCreate = "CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $mysqli->query($sqlCreate);
    echo "Banco de dados `$dbName` OK.\n";

    // 3. Selecionar o banco
    $mysqli->select_db($dbName);

    // 4. Ler e executar o schema.sql
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');

    // MySQLi multi_query permite rodar múltiplos statements
    if ($mysqli->multi_query($sql)) {
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->next_result());
        echo "Banco de dados inicializado com sucesso via MySQLi!\n";
    }

    $mysqli->close();
} catch (mysqli_sql_exception $e) {
    echo "ERRO CRÍTICO: " . $e->getMessage() . "\n";
}
