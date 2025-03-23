<?php
// src/Entity/Assignment.php

declare(strict_types=1);

namespace App\Entity;

/**
 * Entité représentant un devoir
 */
class Assignment
{
    /**
     * @var string L'identifiant unique du devoir
     */
    private ?string $id = null;

    /**
     * @var string Le nom du devoir
     */
    private string $name;

    /**
     * @var string La description du devoir
     */
    private ?string $description;

    /**
     * @var \DateTime La date de début de la période de remise du devoir
     */
    private \DateTime $startPeriod;

    /**
     * @var \DateTime La date de fin de la période de remise du devoir
     */
    private \DateTime $endPeriod;

    /**
     * @var int Le nombre maximum de fichiers pouvant être soumis
     */
    private int $maxFiles;

    /**
     * @var bool Si les soumissions tardives sont autorisées
     */
    private bool $allowLateSubmission;

    /**
     * @var \DateTime La date de création du devoir
     */
    private ?\DateTime $createdAt;

    /**
     * @var \DateTime La date de mise à jour du devoir
     */
    private ?\DateTime $updatedAt;

    /**
     * @var array<int, File> Les fichiers soumis pour le devoir
     */
    public array $files = [];

    /**
     * @var array<int, File> Les fichiers d'instructions du devoir
     */
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

    /**
     * Retourne l'identifiant du devoir.
     *
     * @return string|null L'identifiant du devoir
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Définit l'identifiant du devoir.
     *
     * @param string|null $id L'identifiant du devoir
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Retourne le nom du devoir.
     *
     * @return string Le nom du devoir
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom du devoir.
     *
     * @param string $name Le nom du devoir
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Retourne la description du devoir.
     *
     * @return string|null La description du devoir
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Définit la description du devoir.
     *
     * @param string|null $description La description du devoir
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Retourne la date de début de la période de remise du devoir.
     *
     * @return \DateTime La date de début de la période de remise du devoir
     */
    public function getStartPeriod(): \DateTime
    {
        return $this->startPeriod;
    }

    /**
     * Définit la date de début de la période de remise du devoir.
     *
     * @param \DateTime $startPeriod La date de début de la période de remise du devoir
     */
    public function setStartPeriod(\DateTime $startPeriod): void
    {
        $this->startPeriod = $startPeriod;
    }

    /**
     * Retourne la date de fin de la période de remise du devoir.
     *
     * @return \DateTime La date de fin de la période de remise du devoir
     */
    public function getEndPeriod(): \DateTime
    {
        return $this->endPeriod;
    }

    /**
     * Définit la date de fin de la période de remise du devoir.
     *
     * @param \DateTime $endPeriod La date de fin de la période de remise du devoir
     */
    public function setEndPeriod(\DateTime $endPeriod): void
    {
        $this->endPeriod = $endPeriod;
    }

    /**
     * Retourne le nombre maximum de fichiers pouvant être soumis.
     *
     * @return int Le nombre maximum de fichiers pouvant être soumis
     */
    public function getMaxFiles(): int
    {
        return $this->maxFiles;
    }

    /**
     * Définit le nombre maximum de fichiers pouvant être soumis
     *
     * @param int $maxFile Le nombre maximum de fichiers pouvant être soumis
     */
    public function setMaxFiles(int $maxFile): void
    {
        $this->maxFiles = $maxFile;
    }

    /**
     * Retourne si les soumissions tardives sont autorisées.
     *
     * @return bool Si les soumissions tardives sont autorisées
     */
    public function getAllowLateSubmission(): bool
    {
        return $this->allowLateSubmission;
    }

    /**
     * Définit si les soumissions tardives sont autorisées.
     *
     * @param bool $allowLateSubmission Si les soumissions tardives sont autorisées
     */
    public function setAllowLateSubmission(bool $allowLateSubmission): void
    {
        $this->allowLateSubmission = $allowLateSubmission;
    }

    /**
     * Retourne la date de création du devoir.
     *
     * @return \DateTime|null La date de création du devoir
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création du devoir.
     *
     * @param \DateTime|null $createdAt La date de création du devoir
     */
    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Retourne la date de mise à jour du devoir.
     *
     * @return \DateTime|null La date de mise à jour du devoir
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Définit la date de mise à jour du devoir.
     *
     * @param \DateTime|null $updatedAt La date de mise à jour du devoir
     */
    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Retourne les fichiers soumis pour le devoir.
     *
     * @return array<int, File> Les fichiers soumis pour le devoir
     */
    public function getFiles(): array
    {
        return $this->files ?? [];
    }

    /**
     * Ajoute un fichier soumis pour le devoir.
     *
     * @param File $file Le fichier soumis pour le devoir
     */
    public function addFile(File $file): void
    {
        $this->files[] = $file;
    }

    /**
     * Retourne les fichiers d'instructions du devoir.
     *
     * @return array<int, File> Les fichiers d'instructions du devoir
     */
    public function getInstructionFiles(): array
    {
        return $this->instructionFiles ?? [];
    }

    /**
     * Ajoute un fichier d'instructions du devoir.
     *
     * @param File $file Le fichier d'instructions du devoir
     */
    public function addInstructionFile(File $file): void
    {
        $this->instructionFiles[] = $file;
    }
}
