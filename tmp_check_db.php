<?php
// Usando as credenciais do .env que acabei de ler
$host = '127.0.0.1';
$dbname = 'epi_guard';
$user = 'root';
$pass = '';

try {
    $p = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $p->exec("SET time_zone = '-03:00'"); // Alinhar com PHP
    
    $s = $p->query("SELECT id, nome FROM filiais");
    echo "FILIAIS FOUND:\n";
    print_r($s->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\nCURDATE() in MySQL: " . $p->query("SELECT CURDATE()")->fetchColumn() . "\n";
    echo "NOW() in MySQL: " . $p->query("SELECT NOW()")->fetchColumn() . "\n";
    echo "PHP date('Y-m-d'): " . date('Y-m-d') . "\n";
    
    $s = $p->query("SELECT COUNT(*) FROM ocorrencias WHERE DATE(data_hora) = CURDATE() AND tipo = 'INFRACAO'");
    echo "\nTODAY INFRACTIONS (GLOBAL - ANY FILIAL): " . $s->fetchColumn() . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
