<?php

namespace Facchini\Presentation\Controller\Api;

class BranchApiController
{
    public function switch()
    {
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $filialId = $data['filial_id'] ?? null;
        
        if (!$filialId) {
            echo json_encode(['success' => false, 'message' => 'ID da filial não fornecido.']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['active_filial_id'] = (int)$filialId;
        
        echo json_encode(['success' => true]);
    }

    public function list()
    {
        header('Content-Type: application/json');
        
        $db = \Facchini\Infrastructure\Database\Connection::getInstance();
        $stmt = $db->query("SELECT id, nome FROM filiais ORDER BY id ASC");
        
        $filiais = $stmt->fetchAll();
        
        echo json_encode($filiais);
    }
}
