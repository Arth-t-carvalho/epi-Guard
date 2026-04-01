<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Database\Connection;

class OccurrenceStoreApiController
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function store()
    {
        header('Content-Type: application/json');

        try {
            $funcionarioId = (int) ($_POST['funcionario_id'] ?? 0);
            $epiId = $_POST['epi_id'] ?? 'none';
            $dataHora = $_POST['data_hora'] ?? date('Y-m-d H:i:s');
            $tipoAcao = $_POST['tipo_acao'] ?? '';
            $observacao = $_POST['observacao'] ?? '';
            $originalOccurrenceId = (int) ($_POST['original_occurrence_id'] ?? 0);

            if ($funcionarioId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Funcionário não selecionado.']);
                return;
            }

            // Formatar data
            $dataFormatada = str_replace('T', ' ', $dataHora) . ':00';

            // Se veio de "Assinar Ocorrência", vincula a ação à infração original
            if ($originalOccurrenceId > 0) {
                $ocorrenciaId = $originalOccurrenceId;

                // Atualizar a data_hora da infração original para o que foi selecionado no formulário
                $stmtUpdate = $this->db->prepare("UPDATE ocorrencias SET data_hora = ? WHERE id = ?");
                $stmtUpdate->execute([$dataFormatada, $ocorrenciaId]);
            } else {
                // 1. Inserir na tabela ocorrencias (novo registro)
                $stmt = $this->db->prepare("INSERT INTO ocorrencias (funcionario_id, data_hora, tipo) VALUES (?, ?, 'INFRACAO')");
                $stmt->execute([$funcionarioId, $dataFormatada]);
                $ocorrenciaId = (int) $this->db->lastInsertId();

                // 2. Inserir EPI envolvido (se selecionado)
                if ($epiId !== 'none' && $epiId !== '' && (int) $epiId > 0) {
                    $epiIdInt = (int) $epiId;
                    $stmtEpi = $this->db->prepare("INSERT INTO ocorrencia_epis (ocorrencia_id, epi_id) VALUES (?, ?)");
                    $stmtEpi->execute([$ocorrenciaId, $epiIdInt]);
                }
            }

            // 3. Inserir ação disciplinar (se há tipo de ação)
            if (!empty($tipoAcao)) {
                // Mapear tipo_acao para ENUM da tabela acoes_ocorrencia
                $tipoEnum = 'OBSERVACAO';
                $tipoLower = strtolower($tipoAcao);
                if (strpos($tipoLower, 'verbal') !== false) {
                    $tipoEnum = 'ADVERTENCIA_VERBAL';
                } elseif (strpos($tipoLower, 'escrita') !== false) {
                    $tipoEnum = 'ADVERTENCIA_ESCRITA';
                } elseif (strpos($tipoLower, 'suspens') !== false) {
                    $tipoEnum = 'SUSPENSAO';
                }

                // Usar usuario_id = 1 como padrão (administrador)
                $usuarioId = 1;
                $stmtAcao = $this->db->prepare("INSERT INTO acoes_ocorrencia (ocorrencia_id, usuario_id, tipo, observacao) VALUES (?, ?, ?, ?)");
                $stmtAcao->execute([$ocorrenciaId, $usuarioId, $tipoEnum, $observacao]);
            }

            // 4. Upload de evidências
            if (!empty($_FILES['evidencias'])) {
                $uploadDir = __DIR__ . '/../../../../public/uploads/evidencias/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                foreach ($_FILES['evidencias']['tmp_name'] as $index => $tmpName) {
                    if (empty($tmpName) || $_FILES['evidencias']['error'][$index] !== UPLOAD_ERR_OK) continue;

                    $ext = pathinfo($_FILES['evidencias']['name'][$index], PATHINFO_EXTENSION);
                    $filename = 'ev_' . $ocorrenciaId . '_' . time() . '_' . $index . '.' . $ext;
                    $destPath = $uploadDir . $filename;

                    if (move_uploaded_file($tmpName, $destPath)) {
                        $caminhoRelativo = '/uploads/evidencias/' . $filename;
                        $stmtEv = $this->db->prepare("INSERT INTO evidencias (ocorrencia_id, caminho_imagem) VALUES (?, ?)");
                        $stmtEv->execute([$ocorrenciaId, $caminhoRelativo]);
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Ocorrência registrada com sucesso!',
                'id' => $ocorrenciaId
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ]);
        }
    }
}

