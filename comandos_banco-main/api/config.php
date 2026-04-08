<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$host = '127.0.0.1';
$db   = 'epi_guard';
$user = 'postgres';
$pass = 'F@cchini2025';
$port = '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=disable";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     echo json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]);
     exit;
}
?>
