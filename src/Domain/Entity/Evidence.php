<?php
declare(strict_types = 1)
;

namespace epiGuard\Domain\Entity;

use DateTimeImmutable;

class Evidence
{
    private ?int $id;
    private int $occurrenceId;
    private string $filePath;
    private string $fileType;
    private int $fileSize;
    private DateTimeImmutable $createdAt;

    public function __construct(
        int $occurrenceId,
        string $filePath,
        string $fileType,
        int $fileSize,
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null
        )
    {
        $this->occurrenceId = $occurrenceId;
        $this->filePath = $filePath;
        $this->fileType = $fileType;
        $this->fileSize = $fileSize;
        $this->id = $id;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getOccurrenceId(): int
    {
        return $this->occurrenceId;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
