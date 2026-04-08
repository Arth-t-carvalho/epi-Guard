<?php
try {
    $mysql = new PDO("mysql:host=localhost;dbname=epi_guard", "root", "");
    
    echo "--- FILIAIS (MySQL) ---\n";
    $stmt = $mysql->query("SELECT id, nome FROM filiais");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\n--- USUARIOS (MySQL) ---\n";
    $stmt = $mysql->query("SELECT id, usuario, cargo FROM usuarios");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "ERRO MySQL: " . $e->getMessage();
}
