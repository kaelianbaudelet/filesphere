<?php
// src/Entity/SectionAssignment.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Section;
use App\Entity\assignment;

/**
 * Entité représentant la relation entre une section et un devoir
 */
class Sectionassignment
{
    /**
     * @var Section La section associée au devoir
     */
    private Section $section;

    /**
     * @var assignment Le devoir associé à la section
     */
    private assignment $assignment;

    public function __construct(Section $section, assignment $assignment)
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
     * @return assignment Le devoir associé à la section
     */
    public function getassignment(): assignment
    {
        return $this->assignment;
    }

    /**
     * Définit l'identifiant du devoir.
     *
     * @param assignment $assignment Le devoir associé à la section
     */
    public function setassignment(assignment $assignment): void
    {
        $this->assignment = $assignment;
    }
}
