<?php

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Database\Connection;

class NotificationApiController
{
    public function check()
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        $db = Connection::getInstance();
        $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        
        try {
            if ($last_id === 0) {
                // === INIT: Primeira carga — retorna as 50 infrações mais recentes ===
                $sql = "
                    SELECT 
                        o.id,
                        f.nome AS funcionario_nome,
                        COALESCE(s.sigla, 'N/A') AS setor_sigla,
                        COALESCE(e.nome_en, e.nome, 'PPE') AS epi_nome,
                        DATE_FORMAT(o.data_hora, '%Y-%m-%d %H:%i:%s') AS data_hora
                    FROM ocorrencias o
                    JOIN funcionarios f ON o.funcionario_id = f.id
                    LEFT JOIN setores s ON f.setor_id = s.id
                    LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                    LEFT JOIN epis e ON oe.epi_id = e.id
                    WHERE o.tipo = 'INFRACAO' AND o.oculto = FALSE
                    ORDER BY o.id DESC
                    LIMIT 50
                ";
                
                $result = $db->query($sql);
                
                if ($result === false) {
                    throw new \Exception('Query error: ' . $db->error);
                }
                
                $dados = [];
                while ($row = $result->fetch_assoc()) {
                    $dados[] = $row;
                }
                
                $latestId = !empty($dados) ? (int)$dados[0]['id'] : 0;
                
                echo json_encode([
                    'status'  => 'init',
                    'last_id' => $latestId,
                    'dados'   => $dados
                ]);
                return;
            }
            
            // === POLLING: Busca infrações mais novas que last_id ===
            $stmt = $db->prepare("
                SELECT 
                    o.id,
                    f.nome AS funcionario_nome,
                    COALESCE(s.sigla, 'N/A') AS setor_sigla,
                    COALESCE(e.nome_en, e.nome, 'PPE') AS epi_nome,
                    DATE_FORMAT(o.data_hora, '%Y-%m-%d %H:%i:%s') AS data_hora
                FROM ocorrencias o
                JOIN funcionarios f ON o.funcionario_id = f.id
                LEFT JOIN setores s ON f.setor_id = s.id
                LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                LEFT JOIN epis e ON oe.epi_id = e.id
                WHERE o.id > ? AND o.tipo = 'INFRACAO' AND o.oculto = FALSE
                ORDER BY o.id ASC
            ");
            
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
                'status'  => 'success',
                'last_id' => $newLastId,
                'dados'   => $dados
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
