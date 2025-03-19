<?php

declare(strict_types=1);

namespace App\Entity;

class Section
{
    private ?string $id = null;
    private string $name;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;
    public ?array $assignments = [];

    public function __construct(
        ?string $id,
        string $name,
        ?\DateTime $createdAt,
        ?\DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
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

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getassignments(): array
    {
        return $this->assignments;
    }

    public function addassignment(assignment $assignment): void
    {
        $this->assignments[] = $assignment;
    }
}
