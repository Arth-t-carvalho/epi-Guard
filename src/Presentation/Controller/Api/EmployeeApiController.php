<?php
declare(strict_types=1);

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Persistence\MySQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\MySQLDepartmentRepository;
use Facchini\Domain\Entity\Employee;
use Facchini\Domain\ValueObject\CPF;
use DateTimeImmutable;

class EmployeeApiController
{
    private MySQLEmployeeRepository $repository;
    private MySQLDepartmentRepository $deptRepository;

    public function __construct()
    {
        $this->deptRepository = new MySQLDepartmentRepository();
        $this->repository = new MySQLEmployeeRepository($this->deptRepository);
    }

    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $employees = $this->repository->findAll();
            $data = array_map(function(Employee $e) {
                return [
                    'id' => $e->getId(),
                    'nome' => $e->getName(),
                    'setor_id' => $e->getDepartment()->getId(),
                    'setor_nome' => $e->getDepartment()->getName(),
                    'created_at' => $e->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }, $employees);

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['nome']) || empty($input['setor_id'])) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Nome e Setor são obrigatórios.']);
                return;
            }

            $department = $this->deptRepository->findById((int)$input['setor_id']);
            if (!$department) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Setor não encontrado.']);
                return;
            }

            $employee = new Employee(
                name: trim($input['nome']),
                cpf: new CPF('11144477735'), // Valid dummy CPF
                enrollmentNumber: '',
                department: $department
            );

            $this->repository->save($employee);

            echo json_encode(['success' => true, 'data' => ['id' => $employee->getId()]]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function update(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['id']) || empty($input['nome'])) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'ID e Nome são obrigatórios.']);
                return;
            }

            $id = (int)$input['id'];
            $employee = $this->repository->findById($id);
            if (!$employee) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Funcionário não encontrado.']);
                return;
            }

            $deptId = (int)($input['setor_id'] ?? $employee->getDepartment()->getId());
            $department = $this->deptRepository->findById($deptId);

            $updated = new Employee(
                name: trim($input['nome']),
                cpf: $employee->getCpf(),
                enrollmentNumber: $employee->getEnrollmentNumber(),
                department: $department ?: $employee->getDepartment(),
                id: $id,
                createdAt: $employee->getCreatedAt()
            );

            $this->repository->update($updated);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function delete(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['id'])) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'ID é obrigatório.']);
                return;
            }

            $employee = $this->repository->findById((int)$input['id']);
            if ($employee) {
                $this->repository->delete($employee);
            }

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
