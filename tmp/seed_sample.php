<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
$db = \Facchini\Infrastructure\Database\Connection::getInstance();

$db->query("INSERT INTO ocorrencias (funcionario_id, data_hora, tipo, oculto) VALUES (1, NOW(), 'INFRACAO', 0)");
$occId = $db->insert_id;
$db->query("INSERT INTO ocorrencia_epis (ocorrencia_id, epi_id) VALUES ($occId, 1)");

echo "Created occurrence ID: $occId\n";
