<?php
require 'src/Infrastructure/Database/Connection.php';
$db = \Facchini\Infrastructure\Database\Connection::getInstance();
$stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'funcionarios'");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Columns in funcionarios: " . implode(", ", $columns) . "\n";
