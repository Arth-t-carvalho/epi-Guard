<?php
require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
$db = \Facchini\Infrastructure\Database\Connection::getInstance();

// 1. Simular uma ocorrência
echo "Simulando ocorrência...\n";
$resSim = file_get_contents('http://localhost/epi-Guard/api/simulate-occurrence');
echo "Resposta Simulação: $resSim\n";

// 2. Checar notificações
echo "\nChecando notificações...\n";
$resNotif = file_get_contents('http://localhost/epi-Guard/api/check_notificacoes?last_id=0');
echo "Resposta Notificações: $resNotif\n";
