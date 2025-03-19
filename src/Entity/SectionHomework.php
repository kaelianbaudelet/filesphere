<?php
// src/Entity/Sectionassignment.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Section;
use App\Entity\assignment;

class Sectionassignment
{
    private Section $section;
    private assignment $assignment;

    public function __construct(Section $section, assignment $assignment)
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

    public function getassignment(): assignment
    {
        return $this->assignment;
    }

    public function setassignment(assignment $assignment): void
    {
        $this->assignment = $assignment;
    }
}
