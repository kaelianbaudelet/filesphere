<?php
// src/Entity/SchoolClassStudent.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Entity\SchoolClass;

/**
 * Entité représentant la relation entre un élève et une classe
 */
class SchoolClassStudent
{
    /**
     * @var User L'élève associé à la classe
     */
    private User $student;

    /**
     * @var SchoolClass La classe associée à l'élève
     */
    private SchoolClass $class;

    public function __construct(User $student, SchoolClass $class)
    {
        $this->student = $student;
        $this->class = $class;
    }

    /**
     * Récupère l'identifiant de l'élève.
     *
     * @return User L'élève associé à la classe
     */
    public function getStudent(): User
    {
        return $this->student;
    }

    /**
     * Définit l'identifiant de l'élève.
     *
     * @param User $student L'élève associé à la classe
     */
    public function setStudent(User $student): void
    {
        $this->student = $student;
    }

    /**
     * Récupère l'identifiant de la classe.
     *
     * @return SchoolClass La classe associée à l'élève
     */
    public function getClass(): SchoolClass
    {
        return $this->class;
    }

    /**
     * Définit l'identifiant de la classe.
     *
     * @param SchoolClass $class La classe associée à l'élève
     */
    public function setClass(SchoolClass $class): void
    {
        $this->class = $class;
    }
}
