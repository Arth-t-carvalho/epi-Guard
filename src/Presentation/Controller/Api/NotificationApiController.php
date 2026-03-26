<?php

namespace epiGuard\Presentation\Controller\Api;

class NotificationApiController
{
    public function check()
    {
        header('Content-Type: application/json');
        
        $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        
        // Simulate initialization
        if ($last_id === 0) {
            echo json_encode(['status' => 'init', 'last_id' => 100]);
            return;
        }

        // Return empty data most of the time to avoid spamming UI
        echo json_encode([
            'status' => 'success',
            'dados' => []
        ]);
    }
}
