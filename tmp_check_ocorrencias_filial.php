<?php
$db = new mysqli('127.0.0.1', 'root', '', 'epi_guard');
if ($db->connect_error) die("Erro: " . $db->connect_error);
$res = $db->query('SELECT filial_id, COUNT(*) as total FROM ocorrencias GROUP BY filial_id');
while ($row = $res->fetch_assoc()) {
    echo "Filial ID: " . $row['filial_id'] . " - Total Ocorrências: " . $row['total'] . "\n";
}
if ($res->num_rows === 0) echo "Tabela ocorrencias vazia.\n";
