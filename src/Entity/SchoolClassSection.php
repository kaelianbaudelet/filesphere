<?php
// src/Entity/SchoolClassSection.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Section;
use App\Entity\SchoolClass;

class SchoolClassSection
{
    private Section $section;
    private SchoolClass $class;

    public function __construct(Section $section, SchoolClass $class)
    {
        $this->section = $section;
        $this->class = $class;
    }

    public function getSection(): Section
    {
        return $this->section;
    }

    public function setSection(Section $section): void
    {
        $this->section = $section;
    }

    public function getClass(): SchoolClass
    {
        return $this->class;
    }

    public function setClass(SchoolClass $class): void
    {
        $this->class = $class;
    }
}
