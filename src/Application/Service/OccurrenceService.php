<?php
declare(strict_types = 1)
;

namespace Facchini\Application\Service;

use Facchini\Application\DTO\Request\CreateOccurrenceRequest;
use Facchini\Application\DTO\Request\ResolveOccurrenceRequest;
use Facchini\Application\Validator\OccurrenceValidator;
use Facchini\Domain\Entity\Occurrence;
use Facchini\Domain\Entity\OccurrenceAction;
use Facchini\Domain\Exception\DomainException;
use Facchini\Domain\Exception\InvalidOccurrenceException;
use Facchini\Domain\Exception\EmployeeNotFoundException;
use Facchini\Domain\Repository\EpiRepositoryInterface;
use Facchini\Domain\Repository\OccurrenceRepositoryInterface;
use Facchini\Domain\Repository\EmployeeRepositoryInterface;
use Facchini\Domain\Repository\UserRepositoryInterface;
use Facchini\Domain\ValueObject\ActionType;
use Facchini\Domain\ValueObject\OccurrenceStatus;
use Facchini\Domain\ValueObject\OccurrenceType;
use DateTimeImmutable;

class OccurrenceService
{
    private OccurrenceRepositoryInterface $occurrenceRepository;
    private EmployeeRepositoryInterface $employeeRepository;
    private UserRepositoryInterface $userRepository;
    private EpiRepositoryInterface $epiRepository;
    private OccurrenceValidator $validator;

    public function __construct(
        OccurrenceRepositoryInterface $occurrenceRepository,
        EmployeeRepositoryInterface $employeeRepository,
        UserRepositoryInterface $userRepository,
        EpiRepositoryInterface $epiRepository,
        OccurrenceValidator $validator
        )
    {
        $this->occurrenceRepository = $occurrenceRepository;
        $this->employeeRepository = $employeeRepository;
        $this->userRepository = $userRepository;
        $this->epiRepository = $epiRepository;
        $this->validator = $validator;
    }

    /**
     * @param CreateOccurrenceRequest $request
     * @return Occurrence
     * @throws DomainException
     */
    public function createOccurrence(CreateOccurrenceRequest $request): Occurrence
    {
        $this->validator->validateCreation($request);

        $employee = $this->employeeRepository->findById($request->employeeId);
        if (!$employee) {
            throw EmployeeNotFoundException::withId($request->employeeId);
        }

        $registeredBy = $this->userRepository->findById($request->registeredById);
        if (!$registeredBy) {
            throw new DomainException("Registering user not found.");
        }

        $epiItem = $this->epiRepository->findById($request->epiItemId);
        if (!$epiItem) {
            throw new DomainException("EPI Item not found.");
        }

        $occurrenceType = new OccurrenceType($request->type);
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $request->date);

        $occurrence = new Occurrence(
            $employee,
            $registeredBy,
            $epiItem,
            $occurrenceType,
            $request->description,
            $date
            );

        $this->occurrenceRepository->save($occurrence);

        return $occurrence;
    }

    /**
     * @param ResolveOccurrenceRequest $request
     * @return Occurrence
     * @throws DomainException
     */
    public function resolveOccurrence(ResolveOccurrenceRequest $request): Occurrence
    {
        $occurrence = $this->occurrenceRepository->findById($request->occurrenceId);
        if (!$occurrence) {
            throw new DomainException("Occurrence not found.");
        }

        if (!$occurrence->getStatus()->isOpen() && $occurrence->getStatus()->getValue() !== OccurrenceStatus::IN_PROGRESS) {
            throw InvalidOccurrenceException::invalidStatusTransition(
                $occurrence->getStatus()->getValue(),
                OccurrenceStatus::RESOLVED
            );
        }

        $resolvedBy = $this->userRepository->findById($request->resolvedById);
        if (!$resolvedBy) {
            throw new DomainException("Resolving user not found.");
        }

        $actionType = new ActionType($request->actionType);

        $action = new OccurrenceAction(
            $occurrence->getId(),
            $actionType,
            $request->actionDescription,
            $resolvedBy
            );

        $occurrence->addAction($action);
        $occurrence->changeStatus(new OccurrenceStatus(OccurrenceStatus::RESOLVED));

        $this->occurrenceRepository->update($occurrence);

        return $occurrence;
    }
}
