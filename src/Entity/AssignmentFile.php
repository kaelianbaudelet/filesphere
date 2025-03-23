<?php
// src/Entity/AssignmentFile.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Assignment;
use App\Entity\File;

/**
 * Entité représentant la relation entre un devoir et un fichier
 */
class AssignmentFile
{
    /**
     * @var Assignment Le devoir associé au fichier
     */
    private Assignment $assignment;

    /**
     * @var File Le fichier associé au devoir
     */
    private File $file;

    public function __construct(assignment $assignment, File $file)
    {
        $this->assignment = $assignment;
        $this->file = $file;
    }

    /**
     * Récupère l'identifiant du devoir.
     *
     * @return Assignment Le devoir associé au fichier
     */
    public function getAssignment(): assignment
    {
        return $this->assignment;
    }

    /**
     * Définit l'identifiant du devoir.
     *
     * @param Assignment $assignment Le devoir associé au fichier
     */
    public function setAssignment(assignment $assignment): void
    {
        $this->assignment = $assignment;
    }

    /**
     * Récupère l'identifiant du devoir.
     *
     * @return File Le fichier associé au devoir
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * Définit l'identifiant du devoir.
     *
     * @param File $file Le fichier associé au devoir
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
    }
}
