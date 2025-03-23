<?php
// src/Entity/File.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;

/**
 * Entité représentant un fichier
 */
class File
{
    /**
     * @var string L'identifiant unique du fichier
     */
    private ?string $id = null;

    /**
     * @var string Le token du fichier
     */
    private ?string $token = null;

    /**
     * @var string Le nom du fichier
     */
    private string $name;

    /**
     * @var string L'extension du fichier
     */
    private ?string $extension = null;

    /**
     * @var int La taille du fichier
     */
    private int $size;

    /**
     * @var User L'utilisateur propriétaire du fichier
     */
    private ?User $owner;

    /**
     * @var \DateTime La date de création du fichier
     */
    private ?\DateTime $createdAt;

    /**
     * @var \DateTime La date de mise à jour du fichier
     */
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

    /**
     * Retourne l'identifiant du fichier.
     *
     * @return string|null L'identifiant du fichier
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Définit l'identifiant du fichier.
     *
     * @param string|null $id L'identifiant du fichier
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Retourne le token du fichier.
     *
     * @return string|null Le token du fichier
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Définit le token du fichier.
     *
     * @param string|null $token Le token du fichier
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    /**
     * Retourne le nom du fichier.
     *
     * @return string Le nom du fichier
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom du fichier.
     *
     * @param string $name Le nom du fichier
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Retourne l'extension du fichier.
     *
     * @return string|null L'extension du fichier
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * Définit l'extension du fichier.
     *
     * @param string|null $extension L'extension du fichier
     */
    public function setExtension(?string $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * Retourne la taille du fichier.
     *
     * @return float La taille du fichier
     */
    public function getSize(): float
    {
        return $this->size;
    }

    /**
     * Définit la taille du fichier.
     *
     * @param int $size La taille du fichier
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * Retourne l'utilisateur propriétaire du fichier.
     *
     * @return User|null L'utilisateur propriétaire du fichier
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * Définit l'utilisateur propriétaire du fichier.
     *
     * @param User|null $owner L'utilisateur propriétaire du fichier
     */
    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * Retourne la date de création du fichier.
     *
     * @return \DateTime La date de création du fichier
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création du fichier.
     *
     * @param \DateTime $createdAt La date de création du fichier
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Définit la date de mise à jour du fichier.
     *
     * @param \DateTime $updatedAt La date de mise à jour du fichier
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
