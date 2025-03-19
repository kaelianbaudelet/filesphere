<?php
// src/Entity/User.php

declare(strict_types=1);

namespace App\Entity;

use InvalidArgumentException;

class User
{
    private ?string $id = null;
    private string $role;
    private string $name;
    private string $email;
    private ?string $password;
    private ?bool $isActive = null;
    private ?string $reset_token;
    private ?\DateTime $reset_token_expires_at;
    private ?\DateTime $created_at;
    private ?\DateTime $updated_at;

    public function __construct(
        ?string $id,
        string $role,
        string $name,
        string $email,
        ?string $password,
        ?bool $isActive,
        ?string $reset_token,
        ?\DateTime $reset_token_expires_at,
        ?\DateTime $created_at,
        ?\DateTime $updated_at
    ) {
        $this->id = $id;
        $this->role = $role;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->isActive = $isActive;
        $this->reset_token = $reset_token;
        $this->reset_token_expires_at = $reset_token_expires_at;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): void
    {
        $this->reset_token = $reset_token;
    }

    public function getResetTokenExpiresAt(): ?\DateTime
    {
        return $this->reset_token_expires_at;
    }

    public function setResetTokenExpiresAt(?\DateTime $reset_token_expires_at): void
    {
        $this->reset_token_expires_at = $reset_token_expires_at;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}
