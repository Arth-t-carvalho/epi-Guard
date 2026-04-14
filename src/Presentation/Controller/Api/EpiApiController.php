<?php
declare(strict_types=1);

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Persistence\MySQLEpiRepository;

class EpiApiController
{
    public function updateColors(): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $colors = $input['colors'] ?? [];

        if (empty($colors)) {
            echo json_encode(['success' => false, 'message' => 'Nenhuma cor fornecida']);
            return;
        }

        $repo = new MySQLEpiRepository();
        
        try {
            foreach ($colors as $item) {
                $id = (int)$item['id'];
                $color = $item['color'];
                
                $epi = $repo->findById($id);
                if ($epi) {
                    $epi->setColor($color);
                    $repo->update($epi);
                }
            }
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

