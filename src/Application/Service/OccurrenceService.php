<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\Service;

use epiGuard\Application\DTO\Request\CreateOccurrenceRequest;
use epiGuard\Application\DTO\Request\ResolveOccurrenceRequest;
use epiGuard\Application\Validator\OccurrenceValidator;
use epiGuard\Domain\Entity\Occurrence;
use epiGuard\Domain\Entity\OccurrenceAction;
use epiGuard\Domain\Exception\DomainException;
use epiGuard\Domain\Exception\InvalidOccurrenceException;
use epiGuard\Domain\Exception\EmployeeNotFoundException;
use epiGuard\Domain\Repository\EpiRepositoryInterface;
use epiGuard\Domain\Repository\OccurrenceRepositoryInterface;
use epiGuard\Domain\Repository\EmployeeRepositoryInterface;
use epiGuard\Domain\Repository\UserRepositoryInterface;
use epiGuard\Domain\ValueObject\ActionType;
use epiGuard\Domain\ValueObject\OccurrenceStatus;
use epiGuard\Domain\ValueObject\OccurrenceType;
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
