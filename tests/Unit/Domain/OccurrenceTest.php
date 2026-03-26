<?php
declare(strict_types = 1)
;

namespace Tests\Unit\Domain;

use App\Domain\Entity\Department;
use App\Domain\Entity\EpiItem;
use App\Domain\Entity\Occurrence;
use App\Domain\Entity\Employee;
use App\Domain\Entity\User;
use App\Domain\ValueObject\CPF;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\OccurrenceStatus;
use App\Domain\ValueObject\OccurrenceType;
use App\Domain\ValueObject\UserRole;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class OccurrenceTest extends TestCase
{
    public function testCanCreateAndChangeStatusOfOccurrence(): void
    {
        $department = new Department('Test', 'T01');
        $employee = new Employee('Employee Test', new CPF('12345678909'), '001', $department);
        $user = new User('Admin', new Email('admin@test.com'), 'hash', new UserRole('admin'));
        $epi = new EpiItem('Safety Glasses');

        $occurrence = new Occurrence(
            $employee,
            $user,
            $epi,
            new OccurrenceType('missing_epi'),
            'Employee was working without safety glasses.',
            new DateTimeImmutable()
            );

        $this->assertTrue($occurrence->getStatus()->isOpen());

        $occurrence->changeStatus(new OccurrenceStatus(OccurrenceStatus::RESOLVED));

        $this->assertFalse($occurrence->getStatus()->isOpen());
        $this->assertEquals(OccurrenceStatus::RESOLVED, $occurrence->getStatus()->getValue());
        $this->assertNotNull($occurrence->getUpdatedAt());
    }
}
