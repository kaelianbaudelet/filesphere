<?php

declare(strict_types=1);

namespace App\Entity;

class Assignment
{
    private ?string $id = null;
    private string $name;
    private ?string $description;
    private \DateTime $startPeriod;
    private \DateTime $endPeriod;
    private int $maxFiles;
    private bool $allowLateSubmission;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;
    public ?array $files = [];
    public ?array $instructionFiles = [];

    public function __construct(
        ?string $id,
        string $name,
        ?string $description,
        \DateTime $startPeriod,
        \DateTime $endPeriod,
        int $maxFiles,
        bool $allowLateSubmission,
        ?\DateTime $createdAt,
        ?\DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->startPeriod = $startPeriod;
        $this->endPeriod = $endPeriod;
        $this->maxFiles = $maxFiles;
        $this->allowLateSubmission = $allowLateSubmission;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStartPeriod(): \DateTime
    {
        return $this->startPeriod;
    }

    public function setStartPeriod(\DateTime $startPeriod): void
    {
        $this->startPeriod = $startPeriod;
    }

    public function getEndPeriod(): \DateTime
    {
        return $this->endPeriod;
    }

    public function setEndPeriod(\DateTime $endPeriod): void
    {
        $this->endPeriod = $endPeriod;
    }

    public function getMaxFiles(): int
    {
        return $this->maxFiles;
    }

    public function setMaxFiles(int $maxFile): void
    {
        $this->maxFiles = $maxFile;
    }

    public function getAllowLateSubmission(): bool
    {
        return $this->allowLateSubmission;
    }

    public function setAllowLateSubmission(bool $allowLateSubmission): void
    {
        $this->allowLateSubmission = $allowLateSubmission;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function addFile(File $file): void
    {
        $this->files[] = $file;
    }

    public function getInstructionFiles(): array
    {
        return $this->instructionFiles;
    }

    public function addInstructionFile(File $file): void
    {
        $this->instructionFiles[] = $file;
    }
}
