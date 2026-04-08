<?php
declare(strict_types=1);

namespace Facchini\Presentation\Controller\Api;

use Facchini\Infrastructure\Persistence\MySQLEmployeeRepository;
use Facchini\Infrastructure\Persistence\MySQLDepartmentRepository;
use Facchini\Domain\Entity\Employee;
use Facchini\Domain\Entity\Department;
use Facchini\Domain\ValueObject\CPF;
use Facchini\Infrastructure\Auth\LdapService;
use DateTimeImmutable;

class EmployeeApiController
{
    private MySQLEmployeeRepository $repository;
    private MySQLDepartmentRepository $deptRepository;
    private array $config;

    public function __construct()
    {
        $this->deptRepository = new MySQLDepartmentRepository();
        $this->repository = new MySQLEmployeeRepository($this->deptRepository);
        $this->config = require __DIR__ . '/../../../../config/app.php';
    }

    private function getLdapService(): LdapService
    {
        return new LdapService($this->config['ldap']);
    }

    private function syncEmployeeToAd(Employee $employee): void
    {
        try {
            $username = $this->generateUsername($employee->getName());
            $this->getLdapService()->saveUser($username, [
                'id' => $employee->getId(),
                'name' => $employee->getName(),
                'cpf' => $employee->getCpf()->getValue(),
                'email' => $employee->getEmail() ? $employee->getEmail()->getValue() : "$username@facchini.local",
                'department' => $employee->getDepartment()->getName(),
                'enrollment' => $employee->getEnrollmentNumber()
            ]);
        } catch (\Exception $e) {
            error_log("AD Sync Error: " . $e->getMessage());
        }
    }

    private function generateUsername(string $name): string
    {
        return strtolower(str_replace(' ', '', $name));
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
                cpf: new CPF('11144477735'),
                enrollmentNumber: '',
                department: $department
            );

            $this->repository->save($employee);
            
            // Auto Sync to AD
            $this->syncEmployeeToAd($employee);

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
            
            // Auto Sync to AD
            $this->syncEmployeeToAd($updated);

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
                $username = $this->generateUsername($employee->getName());
                $this->repository->delete($employee);
                // Auto Sync Deletion Visibility
                $this->getLdapService()->deleteUser($username);
            }

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function syncAllToAd(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $employees = $this->repository->findAll();
            foreach ($employees as $emp) {
                $this->syncEmployeeToAd($emp);
            }
            echo json_encode(['success' => true, 'message' => count($employees) . ' alunos sincronizados com o AD.']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
