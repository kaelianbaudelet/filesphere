<?php
// src/Entity/SchoolClassStudent.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Entity\SchoolClass;

class SchoolClassStudent
{
    private User $student;
    private SchoolClass $class;

    public function __construct(User $student, SchoolClass $class)
    {
        $this->student = $student;
        $this->class = $class;
    }

    public function getStudent(): User
    {
        return $this->student;
    }

    public function setStudent(User $student): void
    {
        $this->student = $student;
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
