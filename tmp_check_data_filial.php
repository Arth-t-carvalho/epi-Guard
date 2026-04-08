<?php
$db = new mysqli('127.0.0.1', 'root', '', 'epi_guard');
if ($db->connect_error) die("Erro: " . $db->connect_error);

echo "--- Funcionários por Filial ---\n";
$resEmp = $db->query('SELECT filial_id, COUNT(*) as total FROM funcionarios GROUP BY filial_id');
while ($row = $resEmp->fetch_assoc()) {
    echo "Filial ID: " . $row['filial_id'] . " - Total Funcionários: " . $row['total'] . "\n";
}

echo "\n--- Setores por Filial ---\n";
$resSet = $db->query('SELECT filial_id, COUNT(*) as total FROM setores GROUP BY filial_id');
while ($row = $resSet->fetch_assoc()) {
    echo "Filial ID: " . $row['filial_id'] . " - Total Setores: " . $row['total'] . "\n";
}
