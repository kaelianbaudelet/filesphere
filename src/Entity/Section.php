<?php
// src/Entity/Section.php

declare(strict_types=1);

namespace App\Entity;

/**
 * Entité représentant une section
 */
class Section
{
    /**
     * @var string|null L'identifiant de la section
     */
    private ?string $id = null;

    /**
     * @var string Le nom de la section
     */
    private string $name;

    /**
     * @var \DateTime|null La date de création de la section
     */
    private ?\DateTime $createdAt;

    /**
     * @var \DateTime|null La date de mise à jour de la section
     */
    private ?\DateTime $updatedAt;

    /**
     * @var array Les devoirs de la section
     */
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

    /**
     * Récupère l'identifiant de la section.
     *
     * @return string|null L'identifiant de la section
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Définit l'identifiant de la section.
     *
     * @param string|null $id L'identifiant de la section
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Récupère le nom de la section.
     *
     * @return string Le nom de la section
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom de la section.
     *
     * @param string $name Le nom de la section
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Récupère la date de création de la section.
     *
     * @return \DateTime|null La date de création de la section
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création de la section.
     *
     * @param \DateTime $createdAt La date de création de la section
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Récupère la date de mise à jour de la section.
     *
     * @return \DateTime|null La date de mise à jour de la section
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Définit la date de mise à jour de la section.
     *
     * @param \DateTime $updatedAt La date de mise à jour de la section
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Récupère les devoirs de la section.
     *
     * @return array Les devoirs de la section
     */
    public function getassignments(): array
    {
        return $this->assignments;
    }

    /**
     * Ajoute un devoir à la section.
     *
     * @param assignment $assignment Le devoir à ajouter à la section
     */
    public function addassignment(assignment $assignment): void
    {
        $this->assignments[] = $assignment;
    }
}
