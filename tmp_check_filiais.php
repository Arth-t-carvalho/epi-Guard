<?php
$db = new mysqli('127.0.0.1', 'root', '', 'epi_guard');
if ($db->connect_error) die("Erro: " . $db->connect_error);
$res = $db->query('SELECT * FROM filiais');
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - Nome: " . $row['nome'] . "\n";
}
if ($res->num_rows === 0) echo "Tabela filiais vazia.\n";
