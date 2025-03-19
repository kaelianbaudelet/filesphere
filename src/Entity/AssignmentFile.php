<?php
// src/Entity/assignmentFile.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Assignment;
use App\Entity\File;

class AssignmentFile
{
    private Assignment $assignment;
    private File $file;

    public function __construct(assignment $assignment, File $file)
    {
        $this->assignment = $assignment;
        $this->file = $file;
    }

    public function getAssignment(): assignment
    {
        return $this->assignment;
    }

    public function setAssignment(assignment $assignment): void
    {
        $this->assignment = $assignment;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }
}
