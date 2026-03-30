<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Database\Connection;

class NotificationApiController
{
    public function check()
    {
        header('Content-Type: application/json');
        
        $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        $db = Connection::getInstance();
        
        try {
            if ($last_id === 0) {
                // Primeira carga: busca o maior ID e as últimas 20 infrações
                $resMax = $db->query("SELECT MAX(id) as max_id FROM ocorrencias");
                $maxRow = $resMax->fetch_assoc();
                $latestId = (int)($maxRow['max_id'] ?? 0);

                $sql = "SELECT o.id, f.nome as funcionario_nome, s.sigla as setor_sigla, 
                               e.nome as epi_nome, o.data_hora
                        FROM ocorrencias o
                        JOIN funcionarios f ON o.funcionario_id = f.id
                        LEFT JOIN setores s ON f.setor_id = s.id
                        LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                        LEFT JOIN epis e ON oe.epi_id = e.id
                        WHERE o.tipo = 'INFRACAO' AND o.oculto = FALSE
                        ORDER BY o.id DESC LIMIT 20";

                $res = $db->query($sql);
                $dados = [];
                while ($row = $res->fetch_assoc()) {
                    $dados[] = $row;
                }

                echo json_encode([
                    'status' => 'init',
                    'last_id' => $latestId,
                    'dados' => $dados
                ]);
                return;
            }

            // Polling: busca infrações com id > last_id
            $stmt = $db->prepare(
                "SELECT o.id, f.nome as funcionario_nome, s.sigla as setor_sigla, 
                        e.nome as epi_nome, o.data_hora
                 FROM ocorrencias o
                 JOIN funcionarios f ON o.funcionario_id = f.id
                 LEFT JOIN setores s ON f.setor_id = s.id
                 LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                 LEFT JOIN epis e ON oe.epi_id = e.id
                 WHERE o.id > ? AND o.tipo = 'INFRACAO' AND o.oculto = FALSE
                 ORDER BY o.id ASC"
            );
            $stmt->bind_param('i', $last_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $dados = [];
            while ($row = $result->fetch_assoc()) {
                $dados[] = $row;
            }

            $newLastId = $last_id;
            if (!empty($dados)) {
                $newLastId = (int)end($dados)['id'];
            }

            echo json_encode([
                'status' => 'success',
                'last_id' => $newLastId,
                'dados' => $dados
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
                'dados' => []
            ]);
        }
    }
}
