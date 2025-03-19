<?php
// src/Entity/Sectionassignment.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Section;
use App\Entity\Assignment;

class SectionAssignment
{
    private Section $section;
    private Assignment $assignment;

    public function __construct(Section $section, Assignment $assignment)
    {
        $this->section = $section;
        $this->assignment = $assignment;
    }

    public function getSection(): Section
    {
        return $this->section;
    }

    public function setSection(Section $section): void
    {
        $this->section = $section;
    }

    public function getAssignment(): Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(Assignment $assignment): void
    {
        $this->assignment = $assignment;
    }
}
