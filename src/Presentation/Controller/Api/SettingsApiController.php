<?php
declare(strict_types=1);

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Persistence\MySQLEpiRepository;

class SettingsApiController
{
    private MySQLEpiRepository $epiRepository;

    public function __construct()
    {
        $this->epiRepository = new MySQLEpiRepository();
    }

    public function updateEpiColor()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $id = isset($input['id']) ? (int) $input['id'] : 0;
        $color = $input['color'] ?? null;
        $nomeEn = $input['nome_en'] ?? null;

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        try {
            $epi = $this->epiRepository->findById($id);
            if (!$epi) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'EPI não encontrado.']);
                return;
            }

            if ($color !== null && preg_match('/^#[a-fA-F0-9]{3,8}$/', $color)) {
                $epi->setColor($color);
            }

            if ($nomeEn !== null) {
                $epi->setNameEn(trim($nomeEn));
            }

            $this->epiRepository->update($epi);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function resetColors()
    {
        header('Content-Type: application/json');

        try {
            $success = $this->epiRepository->resetToDefaults();
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateChartStyle()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Não autorizado.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $style = $input['style'] ?? '';

        if (!in_array($style, ['bar', 'line', 'area'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Estilo inválido.']);
            return;
        }

        try {
            $userRepo = new \epiGuard\Infrastructure\Persistence\MySQLUserRepository();
            $success = $userRepo->updateChartPreference((int) $_SESSION['user_id'], $style);
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
