<?php
declare(strict_types = 1)
;

namespace Tests\Unit\Domain;

use App\Domain\Entity\Department;
use App\Domain\Entity\Employee;
use App\Domain\ValueObject\CPF;
use App\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class EmployeeTest extends TestCase
{
    public function testCanCreateEmployeeWithValidData(): void
    {
        $cpf = new CPF('12345678909'); // Example formatted valid CPF if mocked, though validation might fail real algorithm, lets use dummy test
        $department = new Department('System Analysis', 'ADS123', 1);
        $email = new Email('employee@example.com');

        $employee = new Employee(
            'John Doe',
            $cpf,
            '20261001',
            $department,
            $email
            );

        $this->assertEquals('John Doe', $employee->getName());
        $this->assertEquals($cpf, $employee->getCpf());
        $this->assertEquals('20261001', $employee->getEnrollmentNumber());
        $this->assertEquals($department, $employee->getDepartment());
        $this->assertEquals($email, $employee->getEmail());
        $this->assertNotNull($employee->getCreatedAt());
    }
}
