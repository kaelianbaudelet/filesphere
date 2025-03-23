<?php
// src/Entity/SchoolClassSection.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Section;
use App\Entity\SchoolClass;

/**
 * Entité représentant la relation entre une section et une classe
 */
class SchoolClassSection
{
    /**
     * @var Section La section associée à la classe
     */
    private Section $section;

    /**
     * @var SchoolClass La classe associée à la section
     */
    private SchoolClass $class;

    public function __construct(Section $section, SchoolClass $class)
    {
        $this->section = $section;
        $this->class = $class;
    }

    /**
     * Récupère l'identifiant de la section.
     *
     * @return Section La section associée à la classe
     */
    public function getSection(): Section
    {
        return $this->section;
    }

    /**
     * Définit l'identifiant de la section.
     *
     * @param Section $section La section associée à la classe
     */
    public function setSection(Section $section): void
    {
        $this->section = $section;
    }

    /**
     * Récupère l'identifiant de la classe.
     *
     * @return SchoolClass La classe associée à la section
     */
    public function getClass(): SchoolClass
    {
        return $this->class;
    }

    /**
     * Définit l'identifiant de la classe.
     *
     * @param SchoolClass $class La classe associée à la section
     */
    public function setClass(SchoolClass $class): void
    {
        $this->class = $class;
    }
}
