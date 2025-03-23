<?php
// src/Entity/SectionAssignment.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Section;
use App\Entity\Assignment;

/**
 * Entité représentant la relation entre une section et un devoir
 */
class SectionAssignment
{
    /**
     * @var Section La section associée au devoir
     */
    private Section $section;

    /**
     * @var Assignment Le devoir associé à la section
     */
    private Assignment $assignment;

    public function __construct(Section $section, Assignment $assignment)
    {
        $this->section = $section;
        $this->assignment = $assignment;
    }

    /**
     * Récupère l'identifiant de la section.
     *
     * @return Section La section associée au devoir
     */
    public function getSection(): Section
    {
        return $this->section;
    }

    /**
     * Définit l'identifiant de la section.
     *
     * @param Section $section La section associée au devoir
     */
    public function setSection(Section $section): void
    {
        $this->section = $section;
    }

    /**
     * Récupère l'identifiant du devoir.
     *
     * @return Assignment Le devoir associé à la section
     */
    public function getAssignment(): Assignment
    {
        return $this->assignment;
    }

    /**
     * Définit l'identifiant du devoir.
     *
     * @param Assignment $assignment Le devoir associé à la section
     */
    public function setAssignment(Assignment $assignment): void
    {
        $this->assignment = $assignment;
    }
}
