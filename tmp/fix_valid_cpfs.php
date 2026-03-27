<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';

function generateValidCPF() {
    $n1 = rand(0, 9); $n2 = rand(0, 9); $n3 = rand(0, 9);
    $n4 = rand(0, 9); $n5 = rand(0, 9); $n6 = rand(0, 9);
    $n7 = rand(0, 9); $n8 = rand(0, 9); $n9 = rand(0, 9);

    $d1 = $n9 * 2 + $n8 * 3 + $n7 * 4 + $n6 * 5 + $n5 * 6 + $n4 * 7 + $n3 * 8 + $n2 * 9 + $n1 * 10;
    $d1 = 11 - ($d1 % 11);
    if ($d1 >= 10) $d1 = 0;

    $d2 = $d1 * 2 + $n9 * 3 + $n8 * 4 + $n7 * 5 + $n6 * 6 + $n5 * 7 + $n4 * 8 + $n3 * 9 + $n2 * 10 + $n1 * 11;
    $d2 = 11 - ($d2 % 11);
    if ($d2 >= 10) $d2 = 0;

    return "$n1$n2$n3$n4$n5$n6$n7$n8$n9$d1$d2";
}

try {
    $db = \epiGuard\Infrastructure\Database\Connection::getInstance();
    $result = $db->query("SELECT id FROM funcionarios");
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $cpf = generateValidCPF();
        $stmt = $db->prepare("UPDATE funcionarios SET cpf = ? WHERE id = ?");
        $stmt->bind_param('si', $cpf, $row['id']);
        $stmt->execute();
        $count++;
    }
    echo "Sucesso: $count CPFs válidos foram gerados e atualizados no banco de dados.\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
