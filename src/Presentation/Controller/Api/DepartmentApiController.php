<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository;
use epiGuard\Domain\Entity\Department;

class DepartmentApiController
{
    /**
     * GET /api/departments — Lista todos os setores
     */
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $repo = new MySQLDepartmentRepository();
            $departments = $repo->findAll();

            $data = array_map(function (Department $dept) {
                return [
                    'id'    => $dept->getId(),
                    'nome'  => $dept->getName(),
                    'sigla' => $dept->getCode(),
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
            if (empty($input['nome'])) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'O nome do setor é obrigatório.']);
                return;
            }

            $nome = trim($input['nome']);
            $sigla = trim($input['sigla'] ?? '');
            $epis = $input['epis'] ?? [];

            $repo = new MySQLDepartmentRepository();

            // Verificar duplicata por nome
            if ($repo->findByName($nome)) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Já existe um setor cadastrado com este nome.']);
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
                epis: $epis
            );

            $repo->save($department);

            // Salvar funcionários importados (se houver)
            if (!empty($input['funcionarios']) && is_array($input['funcionarios'])) {
                $employeeRepo = new \epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository($repo);
                foreach ($input['funcionarios'] as $nomeFunc) {
                    if (empty($nomeFunc) || strlen($nomeFunc) < 2) continue;
                    
                    $employee = new \epiGuard\Domain\Entity\Employee(
                        name: trim($nomeFunc),
                        cpf: new \epiGuard\Domain\ValueObject\CPF('11144477735'), // Valid dummy CPF
                        enrollmentNumber: '', // Será gerado ou preenchido depois
                        department: $department
                    );
                    $employeeRepo->save($employee);
                }
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'id'    => $department->getId(),
                    'nome'  => $department->getName(),
                    'sigla' => $department->getCode(),
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

            if (empty($input['id']) || empty($input['nome'])) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'ID e nome são obrigatórios para atualizar.']);
                return;
            }

            $id = (int)$input['id'];
            $nome = trim($input['nome']);
            $sigla = trim($input['sigla'] ?? '');
            $epis = $input['epis'] ?? [];

            $repo = new MySQLDepartmentRepository();
            $department = $repo->findById($id);

            if (!$department) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Setor não encontrado.']);
                return;
            }

            // Validar nome duplicado (exceto se for o próprio setor)
            $existing = $repo->findByName($nome);
            if ($existing && $existing->getId() !== $id) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Já existe outro setor cadastrado com este nome.']);
                return;
            }

            $updatedDept = new Department(
                name: $nome,
                code: $sigla,
                epis: $epis,
                id: $id
            );

            $repo->update($updatedDept);

            // Salvar novos funcionários importados (se houver)
            if (!empty($input['funcionarios']) && is_array($input['funcionarios'])) {
                $employeeRepo = new \epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository($repo);
                foreach ($input['funcionarios'] as $nomeFunc) {
                    if (empty($nomeFunc) || strlen($nomeFunc) < 2) continue;
                    
                    $employee = new \epiGuard\Domain\Entity\Employee(
                        name: trim($nomeFunc),
                        cpf: new \epiGuard\Domain\ValueObject\CPF('11144477735'),
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

    /**
     * GET /api/departments/employees?id=X — Lista funcionários de um setor
     */
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
            $employeeRepo = new \epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository($deptRepo);
            
            $employees = $employeeRepo->findByDepartment($id);
            $names = array_map(fn($e) => $e->getName(), $employees);

            echo json_encode(['success' => true, 'data' => $names]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
