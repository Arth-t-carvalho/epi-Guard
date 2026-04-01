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
                $resMax = $db->query("SELECT MAX(id) as max_id FROM ocorrencias");
                $maxRow = $resMax->fetch();
                $latestId = (int)($maxRow['max_id'] ?? 0);

                $sql = "SELECT o.id, f.nome as funcionario_nome, 
                               s.sigla as setor_sigla, 
                               e.nome as epi_nome, o.data_hora
                        FROM ocorrencias o
                        JOIN funcionarios f ON o.funcionario_id = f.id
                        LEFT JOIN setores s ON f.setor_id = s.id
                        LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                        LEFT JOIN epis e ON oe.epi_id = e.id
                        WHERE o.tipo = 'INFRACAO' AND o.oculto = FALSE
                        ORDER BY o.id DESC LIMIT 20";

                $stmt = $db->query($sql);
                $dados = $stmt->fetchAll();

                echo json_encode([
                    'status' => 'init',        
                    'last_id' => $latestId,    
                    'dados' => $dados          
                ]);
                return;
            }

            $stmt = $db->prepare(
                "SELECT o.id, f.nome as funcionario_nome, 
                        s.sigla as setor_sigla, 
                        e.nome as epi_nome, o.data_hora
                 FROM ocorrencias o
                 JOIN funcionarios f ON o.funcionario_id = f.id
                 LEFT JOIN setores s ON f.setor_id = s.id
                 LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                 LEFT JOIN epis e ON oe.epi_id = e.id
                 WHERE o.id > ? AND o.tipo = 'INFRACAO' AND o.oculto = FALSE
                 ORDER BY o.id ASC"
            );
            $stmt->execute([$last_id]);
            $dados = $stmt->fetchAll();

            $newLastId = $last_id;
            if (!empty($dados)) {
                $lastItem = end($dados);
                $newLastId = (int)$lastItem['id'];
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

