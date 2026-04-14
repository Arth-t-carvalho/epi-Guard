<?php

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Persistence\MySQLDepartmentRepository;
use Facchini\Domain\Entity\Department;
use Facchini\Infrastructure\Database\Connection;

class DepartmentApiController
{
    /**
     * GET /api/departments — Lista todos os setores
     */
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $activeFilialId = $_SESSION['active_filial_id'] ?? 1;
            $repo = new MySQLDepartmentRepository();
            $departments = $repo->findAll($activeFilialId);

            $data = array_map(function (Department $dept) {
                return [
                    'id'      => $dept->getId(),
                    'nome'    => $dept->getName(),
                    'nome_en' => $dept->getNameEn(),
                    'sigla'   => $dept->getCode(),
                ];
            }, $departments);

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/departments/create — Cria um novo setor
     */
    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validação
            if (!isset($input['nome']) || trim($input['nome']) === '') {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'O nome do setor é obrigatório.']);
                return;
            }

            $nome = trim($input['nome']);
            $nomeEn = trim($input['nome_en'] ?? '');
            $sigla = trim($input['sigla'] ?? '');
            $epis = $input['epis'] ?? [];

            $repo = new MySQLDepartmentRepository();

            $activeFilial = $_SESSION['active_filial_id'] ?? 1;
            // Verificar duplicata por nome na mesma filial
            $stmt = \Facchini\Infrastructure\Database\Connection::getInstance()->prepare("SELECT id FROM setores WHERE nome = ? AND filial_id = ?");
            $stmt->execute([$nome, $activeFilial]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Já existe um setor cadastrado com este nome nesta filial.']);
                return;
            }

            // Verificar duplicata por sigla
            if (!empty($sigla) && $repo->findByCode($sigla)) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Já existe um setor com essa sigla.']);
                return;
            }

            $department = new Department(
                name: $nome,
                code: $sigla,
                epis: $epis,
                nameEn: !empty($nomeEn) ? $nomeEn : null,
                filialId: $activeFilial);

            $repo->save($department);

            // Salvar funcionários importados (se houver)
            if (!empty($input['employees']) && is_array($input['employees'])) {
                $employeeRepo = new \Facchini\Infrastructure\Persistence\MySQLEmployeeRepository($repo);
                foreach ($input['employees'] as $nomeFunc) {
                    if (empty($nomeFunc) || strlen($nomeFunc) < 2) continue;
                    
                    $employee = new \Facchini\Domain\Entity\Employee(
                        name: trim($nomeFunc),
                        cpf: new \Facchini\Domain\ValueObject\CPF('11144477735'), // Valid dummy CPF
                        enrollmentNumber: '', // Será gerado ou preenchido depois
                        department: $department
                    );
                    $employeeRepo->save($employee);
                }
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'id'      => $department->getId(),
                    'nome'    => $department->getName(),
                    'nome_en' => $department->getNameEn(),
                    'sigla'   => $department->getCode(),
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/departments/update — Atualiza um setor existente
     */
    public function update(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['id']) || !isset($input['nome']) || trim($input['nome']) === '') {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'ID e nome são obrigatórios para atualizar.']);
                return;
            }

            $id = (int)$input['id'];
            $nome = trim($input['nome']);
            $nomeEn = trim($input['nome_en'] ?? '');
            $sigla = trim($input['sigla'] ?? '');
            $epis = $input['epis'] ?? [];

            $repo = new MySQLDepartmentRepository();
            $department = $repo->findById($id);

            if (!$department) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Setor não encontrado.']);
                return;
            }

            // Validar nome duplicado (exceto se for o próprio setor) dentro da mesma filial
            $activeFilialId = $_SESSION['active_filial_id'] ?? 1;
            $stmt = \Facchini\Infrastructure\Database\Connection::getInstance()->prepare("SELECT id FROM setores WHERE nome = ? AND filial_id = ? AND id <> ?");
            $stmt->execute([$nome, $activeFilialId, $id]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Já existe outro setor cadastrado com este nome nesta filial.']);
                return;
            }

            $updatedDept = new Department(
                name: $nome,
                code: $sigla,
                epis: $epis,
                nameEn: !empty($nomeEn) ? $nomeEn : null,
                filialId: $activeFilialId,
                id: $id
            );

            $repo->update($updatedDept);

            // Salvar novos funcionários importados (se houver)
            if (!empty($input['employees']) && is_array($input['employees'])) {
                $employeeRepo = new \Facchini\Infrastructure\Persistence\MySQLEmployeeRepository($repo);
                foreach ($input['employees'] as $nomeFunc) {
                    if (empty($nomeFunc) || strlen($nomeFunc) < 2) continue;
                    
                    $employee = new \Facchini\Domain\Entity\Employee(
                        name: trim($nomeFunc),
                        cpf: new \Facchini\Domain\ValueObject\CPF('11144477735'),
                        enrollmentNumber: '',
                        department: $updatedDept
                    );
                    $employeeRepo->save($employee);
                }
            }

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/departments/delete — Exclui um setor
     */
    public function delete(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['id'])) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'ID do setor é obrigatório.']);
                return;
            }

            $id = (int)$input['id'];
            $repo = new MySQLDepartmentRepository();
            
            $department = $repo->findById($id);
            if (!$department) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Setor não encontrado.']);
                return;
            }

            $repo->delete($department);

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function employees(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID inválido']);
                return;
            }

            $deptRepo = new MySQLDepartmentRepository();
            $employeeRepo = new \Facchini\Infrastructure\Persistence\MySQLEmployeeRepository($deptRepo);
            
            $employees = $employeeRepo->findByDepartment($id);
            $data = array_map(fn($e) => [
                'id'   => $e->getId(),
                'nome' => $e->getName()
            ], $employees);

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
