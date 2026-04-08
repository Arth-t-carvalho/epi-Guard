<?php
$db = new mysqli('127.0.0.1', 'root', '', 'epi_guard');
if ($db->connect_error) die("Erro: " . $db->connect_error);
$res = $db->query("SELECT id, funcionario_id, tipo, data_hora, filial_id, oculto FROM ocorrencias WHERE filial_id = 6");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
if ($res->num_rows === 0) echo "Nenhum dado para Filial 6.\n";
