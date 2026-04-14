<?php
declare(strict_types=1);

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Persistence\MySQLEpiRepository;

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

        // Se id for menor que -1 ou bater no else. -1 é usado como trigger do Total.
        if (!isset($id) || (int)$id === 0 || (int)$id < -1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        try {
            $db = \Facchini\Infrastructure\Database\Connection::getInstance();
            
            // Validação Back-end contra Cores Duplicadas (EPIs + Gráfico Total)
            if ($color !== null && preg_match('/^#[a-fA-F0-9]{3,8}$/', $color)) {
                $color = strtoupper($color); // padronizar cor uppercase na gravação para as verificações
                
                // Ver se algum outro EPI está usando essa cor
                $stmtCheck = $db->prepare("SELECT id FROM epis WHERE cor ILIKE ? AND id != ?");
                $stmtCheck->execute([$color, $id]);
                if ($stmtCheck->fetch()) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'message' => 'Esta cor já está associada a um equipamento. Use uma cor exclusiva.']);
                    return;
                }

                // Ver se a filial já usa essa cor no gráfico Total
                if ($id !== -1) {
                    $activeFilialId = $_SESSION['active_filial_id'] ?? 1;
                    $stmtCheckTotal = $db->prepare("SELECT id FROM filiais WHERE cor_grafico_total ILIKE ? AND id = ?");
                    $stmtCheckTotal->execute([$color, $activeFilialId]);
                    if ($stmtCheckTotal->fetch()) {
                        http_response_code(409);
                        echo json_encode(['success' => false, 'message' => 'Esta cor já é a cor padrão do gráfico Geral (Total). Use uma cor exclusiva.']);
                        return;
                    }
                }
            }

            // Handler especial para o "Total" (ID -1 injetado pelo Frontend)
            if ($id === -1) {
                if ($color !== null) {
                    $activeFilialId = $_SESSION['active_filial_id'] ?? 1;
                    $stmt = $db->prepare("UPDATE filiais SET cor_grafico_total = ? WHERE id = ?");
                    $stmt->execute([$color, $activeFilialId]);
                }
                echo json_encode(['success' => true]);
                return;
            }

            $epi = $this->epiRepository->findById($id);
            if (!$epi) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'EPI não encontrado.']);
                return;
            }

            if ($color !== null) {
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
            $userRepo = new \Facchini\Infrastructure\Persistence\MySQLUserRepository();
            $success = $userRepo->updateChartPreference((int) $_SESSION['user_id'], $style);
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
