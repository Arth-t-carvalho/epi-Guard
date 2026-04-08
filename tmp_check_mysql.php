<?php
try {
    $mysql = new PDO("mysql:host=localhost;dbname=epi_guard", "root", "");
    
    echo "--- SETORES (MySQL) ---\n";
    $stmt = $mysql->query("SELECT id, nome FROM setores");
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    }
    
    echo "\n--- FUNCIONARIOS (MySQL) ---\n";
    $stmt = $mysql->query("SELECT id, nome FROM funcionarios LIMIT 5");
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    }

} catch (Exception $e) {
    echo "ERRO MySQL: " . $e->getMessage();
}
