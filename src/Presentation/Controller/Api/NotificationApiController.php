<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Database\Connection;

class NotificationApiController
{
    public function check()
    {
        header('Content-Type: application/json');

        $db = Connection::getInstance();
        
        // Buscar todas as infrações não lidas
        $result = $db->query("
            SELECT 
                o.id,
                o.data_hora,
                f.nome AS funcionario_nome,
                s.nome AS setor_nome,
                GROUP_CONCAT(e.nome ORDER BY e.nome SEPARATOR ', ') AS epis
            FROM ocorrencias o
            JOIN funcionarios f ON o.funcionario_id = f.id
            LEFT JOIN setores s ON f.setor_id = s.id
            LEFT JOIN ocorrencia_epis oe ON o.id = oe.ocorrencia_id
            LEFT JOIN epis e ON oe.epi_id = e.id
            WHERE o.tipo = 'INFRACAO' AND o.lida = 0
            GROUP BY o.id, o.data_hora, f.nome, s.nome
            ORDER BY o.id DESC
            LIMIT 50
        ");

        $notifications = [];
        $maxId = 0;

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $notifications[] = [
                    'id'              => (int) $row['id'],
                    'funcionario'     => $row['funcionario_nome'],
                    'setor'           => $row['setor_nome'] ?? 'Sem setor',
                    'epis'            => $row['epis'] ?? 'EPI nao identificado',
                    'data_hora'       => $row['data_hora'],
                    'tempo'           => $this->timeAgo($row['data_hora']),
                ];
                if ((int) $row['id'] > $maxId) {
                    $maxId = (int) $row['id'];
                }
            }
        }

        echo json_encode([
            'status'  => 'success',
            'count'   => count($notifications),
            'last_id' => $maxId,
            'dados'   => $notifications,
        ]);
    }

    public function markAsRead()
    {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID invalido']);
            return;
        }

        $db = Connection::getInstance();
        $stmt = $db->prepare("UPDATE ocorrencias SET lida = 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $db->error]);
        }
    }

    private function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        if ($diff < 60) return 'agora mesmo';
        if ($diff < 3600) return floor($diff / 60) . ' min atrás';
        if ($diff < 86400) return floor($diff / 3600) . 'h atrás';
        return date('d/m H:i', strtotime($datetime));
    }
}
