<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $port = 3308;
    $mysqli = new mysqli("127.0.0.1", "root", "", "epi_guard", $port);
    echo "Conexão bem-sucedida!";
} catch (mysqli_sql_exception $e) {
    echo "Erro: " . $e->getMessage();
}
