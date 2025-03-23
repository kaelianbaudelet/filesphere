<?php
// src/Entity/SchoolClass.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Entity\File;

/**
 * Entité représentant une classe
 */
class SchoolClass
{
    /**
     * @var string L'identifiant unique de la classe
     */
    private ?string $id = null;

    /**
     * @var User L'utilisateur enseignant de la classe
     */
    private User $teacher;

    /**
     * @var File L'icône de la classe
     */
    private ?File $icon;

    /**
     * @var string La couleur de la classe
     */
    private string $color;

    /**
     * @var string Le nom de la classe
     */
    private string $name;

    /**
     * @var \DateTime La date de création de la classe
     */
    private ?\DateTime $created_at;

    /**
     * @var \DateTime La date de mise à jour de la classe
     */
    private ?\DateTime $updated_at;

    /**
     * @var array Les élèves de la classe
     */
    public ?array $students = [];

    /**
     * @var array Les sections de la classe
     */
    public ?array $sections = [];

    public function __construct(
        ?string $id,
        User $teacher,
        ?File $icon,
        string $color,
        string $name,
        ?\DateTime $created_at,
        ?\DateTime $updated_at
    ) {
        $this->id = $id;
        $this->teacher = $teacher;
        $this->icon = $icon;
        $this->color = $color;
        $this->name = $name;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * Récupère l'identifiant de la classe.
     *
     * @return string|null L'identifiant de la classe
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Définit l'identifiant de la classe.
     *
     * @param string|null $id L'identifiant de la classe
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Récupère l'enseignant de la classe.
     *
     * @return User L'utilisateur enseignant de la classe
     */
    public function getTeacher(): User
    {
        return $this->teacher;
    }

    /**
     * Définit l'enseignant de la classe.
     *
     * @param User $teacher L'utilisateur enseignant de la classe
     */
    public function setTeacherId(User $teacher): void
    {
        $this->teacher = $teacher;
    }

    /**
     * Récupère l'icône de la classe.
     *
     * @return File|null L'icône de la classe
     */
    public function getIcon(): ?File
    {
        return $this->icon;
    }

    /**
     * Définit l'icône de la classe.
     *
     * @param File|null $icon L'icône de la classe
     */
    public function setIcon(?File $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * Récupère la couleur de la classe.
     *
     * @return string La couleur de la classe
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Définit la couleur de la classe.
     *
     * @param string $color La couleur de la classe
     */
    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * Récupère le nom de la classe.
     *
     * @return string Le nom de la classe
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom de la classe.
     *
     * @param string $name Le nom de la classe
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Récupère la date de création de la classe.
     *
     * @return \DateTime|null La date de création de la classe
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    /**
     * Définit la date de création de la classe.
     *
     * @param \DateTime|null $created_at La date de création de la classe
     */
    public function setCreatedAt(?\DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * Récupère la date de mise à jour de la classe.
     *
     * @return \DateTime|null La date de mise à jour de la classe
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    /**
     * Définit la date de mise à jour de la classe.
     *
     * @param \DateTime|null $updated_at La date de mise à jour de la classe
     */
    public function setUpdatedAt(?\DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    /**
     * Ajoute un élève à la classe.
     *
     * @param User $student L'élève à ajouter à la classe
     */
    public function addStudent(User $student): void
    {
        $this->students[] = $student;
    }

    /**
     * Récupère les élèves de la classe.
     *
     * @return array|null Les élèves de la classe
     */
    public function getStudents(): ?array
    {
        return $this->students;
    }

    /**
     * Ajoute une section à la classe.
     *
     * @param Section $section La section à ajouter à la classe
     */
    public function getSections(): ?array
    {
        return $this->sections;
    }

    /**
     * Récupère les sections de la classe.
     *
     * @return array|null Les sections de la classe
     */
    public function addSection(Section $section): void
    {
        $this->sections[] = $section;
    }
}
