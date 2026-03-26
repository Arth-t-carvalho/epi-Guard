<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\Service;

use epiGuard\Application\DTO\Request\CreateEmployeeRequest;
use epiGuard\Application\Validator\EmployeeValidator;
use epiGuard\Domain\Entity\Employee;
use epiGuard\Domain\Exception\DomainException;
use epiGuard\Domain\Repository\DepartmentRepositoryInterface;
use epiGuard\Domain\Repository\EmployeeRepositoryInterface;
use epiGuard\Domain\ValueObject\CPF;
use epiGuard\Domain\ValueObject\Email;

class EmployeeService
{
    private EmployeeRepositoryInterface $employeeRepository;
    private DepartmentRepositoryInterface $departmentRepository;
    private EmployeeValidator $validator;

    public function __construct(
        EmployeeRepositoryInterface $employeeRepository,
        DepartmentRepositoryInterface $departmentRepository,
        EmployeeValidator $validator
        )
    {
        $this->employeeRepository = $employeeRepository;
        $this->departmentRepository = $departmentRepository;
        $this->validator = $validator;
    }

    /**
     * @param CreateEmployeeRequest $request
     * @return Employee
     * @throws DomainException
     */
    public function createEmployee(CreateEmployeeRequest $request): Employee
    {
        $this->validator->validateCreation($request);

        try {
            $cpf = new CPF($request->cpf);
        }
        catch (\InvalidArgumentException $e) {
            throw new DomainException("Invalid CPF format provided.");
        }

        if ($this->employeeRepository->findByCpf($cpf)) {
            throw new DomainException("A employee with this CPF already exists.");
        }

        if ($this->employeeRepository->findByEnrollmentNumber($request->enrollmentNumber)) {
            throw new DomainException("A employee with this enrollment number already exists.");
        }

        $department = $this->departmentRepository->findById($request->departmentId);
        if (!$department) {
            throw new DomainException("The specified department does not exist.");
        }

        $email = null;
        if ($request->email) {
            try {
                $email = new Email($request->email);
            }
            catch (\InvalidArgumentException $e) {
                throw new DomainException("Invalid email format provided.");
            }
        }

        $employee = new Employee(
            $request->name,
            $cpf,
            $request->enrollmentNumber,
            $department,
            $email
            );

        $this->employeeRepository->save($employee);

        return $employee;
    }
}
