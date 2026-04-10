<?php
require 'src/Infrastructure/Database/Connection.php';
$db = \Facchini\Infrastructure\Database\Connection::getInstance();
$stmt = $db->query("SELECT id, cor_grafico_total FROM filiais");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
