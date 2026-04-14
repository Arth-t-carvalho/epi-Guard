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
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $activeFilial = $_SESSION['active_filial_id'] ?? 1;
        
        try {
            if ($last_id === 0) {
                // === INIT: Primeira carga — retorna as 50 infrações mais recentes ===
                $sql = "
                    SELECT 
                        o.id,
                        o.funcionario_id,
                        f.nome AS funcionario_nome,
                        COALESCE(s.sigla, 'N/A') AS setor_sigla,
                        COALESCE(e.nome, 'PPE') AS epi_nome,
                        DATE_FORMAT(o.data_hora, '%Y-%m-%d %H:%i:%s') AS data_hora
                    FROM ocorrencias o
                    JOIN funcionarios f ON o.funcionario_id = f.id
                    LEFT JOIN setores s ON f.setor_id = s.id
                    LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                    LEFT JOIN epis e ON oe.epi_id = e.id
                    WHERE o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL) AND o.filial_id = :filial
                    ORDER BY o.id DESC
                    LIMIT 50
                ";
                
                $stmt = $db->prepare($sql);
                $stmt->execute(['filial' => (int)$activeFilial]);
                $dados = $stmt->fetchAll();
                
                $latestId = !empty($dados) ? (int)$dados[0]['id'] : 0;
                
                echo json_encode([
                    'status'  => 'init',
                    'last_id' => $latestId,
                    'dados'   => $dados
                ]);
                return;
            }
            
            // === POLLING: Busca infrações mais novas que last_id ===
            $sqlPolling = "
                SELECT 
                    o.id,
                    o.funcionario_id,
                    f.nome AS funcionario_nome,
                    COALESCE(s.sigla, 'N/A') AS setor_sigla,
                    COALESCE(e.nome, 'PPE') AS epi_nome,
                    DATE_FORMAT(o.data_hora, '%Y-%m-%d %H:%i:%s') AS data_hora
                FROM ocorrencias o
                JOIN funcionarios f ON o.funcionario_id = f.id
                LEFT JOIN setores s ON f.setor_id = s.id
                LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
                LEFT JOIN epis e ON oe.epi_id = e.id
                WHERE o.id > :last_id AND o.tipo = 'INFRACAO' AND (o.oculto = 0 OR o.oculto IS NULL) AND o.filial_id = :filial
                ORDER BY o.id ASC
            ";

            $stmt = $db->prepare($sqlPolling);
            $stmt->execute(['last_id' => $last_id, 'filial' => $activeFilial]);
            $dados = $stmt->fetchAll();
            
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
