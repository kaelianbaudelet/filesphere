<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;

class File
{
    private ?string $id = null;
    private ?string $token = null;
    private string $name;
    private ?string $extension = null;
    private int $size;
    private ?User $owner;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;

    public function __construct(
        ?string $id,
        ?string $token,
        string $name,
        ?string $extension,
        int $size,
        User $owner,
        ?\DateTime $createdAt,
        ?\DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->token = $token;
        $this->name = $name;
        $this->extension = $extension;
        $this->size = $size;
        $this->owner = $owner;
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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): void
    {
        $this->extension = $extension;
    }

    public function getSize(): float
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
