<?php
/**
 * Arquivo de diagnóstico — acesse via: http://localhost/epi-Guard-my/test_notif.php
 * DELETE este arquivo após testar!
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/app.php';

define('BASE_PATH', '/epi-Guard-my');

header('Content-Type: text/plain; charset=utf-8');

$db = \epiGuard\Infrastructure\Database\Connection::getInstance();

echo "=== TESTE DE NOTIFICAÇÕES ===\n\n";

// 1. Contar ocorrências totais
$r1 = $db->query("SELECT COUNT(*) as total FROM ocorrencias WHERE tipo='INFRACAO' AND oculto=FALSE");
$total = $r1->fetch_assoc()['total'];
echo "Total de infrações no banco: {$total}\n\n";

// 2. Buscar as 5 mais recentes
$r2 = $db->query("
    SELECT o.id, f.nome as funcionario_nome, COALESCE(s.sigla,'N/A') as setor_sigla, 
           COALESCE(e.nome,'EPI') as epi_nome, o.data_hora
    FROM ocorrencias o
    JOIN funcionarios f ON o.funcionario_id = f.id
    LEFT JOIN setores s ON f.setor_id = s.id
    LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
    LEFT JOIN epis e ON oe.epi_id = e.id
    WHERE o.tipo='INFRACAO' AND o.oculto=FALSE
    ORDER BY o.id DESC LIMIT 5
");

echo "Últimas 5 infrações:\n";
while ($row = $r2->fetch_assoc()) {
    echo "  ID={$row['id']} | {$row['funcionario_nome']} | {$row['setor_sigla']} | {$row['epi_nome']} | {$row['data_hora']}\n";
}

echo "\n=== FIM DO TESTE ===\n";
echo "\nSe você ver dados acima, a API de notificações DEVE funcionar.\n";
echo "Abra: http://localhost/epi-Guard-my/api/check_notificacoes?last_id=0\n";
