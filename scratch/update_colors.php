<?php
require __DIR__ . '/../src/Infrastructure/Database/Connection.php';
$db = Facchini\Infrastructure\Database\Connection::getInstance();
$db->exec("UPDATE filiais SET cor_grafico_total = '#10B981'");
echo "Cor do Total atualizada para #10B981 (Verde Esmeralda).\n";
