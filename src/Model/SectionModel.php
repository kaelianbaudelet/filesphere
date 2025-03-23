<?php
// src/Model/SectionModel.php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Section;
use PDO;

/**
 * Modèle en charge de la gestion des sections
 */
class SectionModel
{
    /**
     * @var PDO Instance de la classe PDO
     */
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Crée une section
     *
     * @param Section $section La section
     */
    public function createSection(Section $section): bool
    {
        $sql = "INSERT INTO Section (name, created_at, updated_at) VALUES
(:name, :created_at, :updated_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $section->getName());
        $stmt->bindValue(':created_at', $section->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $section->getUpdatedAt()->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }

    /**
     * Récupère une section par son identifiant
     *
     * @param string $id L'identifiant de la section
     */
    public function getSectionById(string $id): ?Section
    {
        $sql = "SELECT * FROM Section WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new Section(
            $row['id'],
            $row['name'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }

    /**
     * Met à jour une section
     *
     * @param Section $section La section
     */
    public function updateSection(Section $section): bool
    {
        $sql = "UPDATE Section SET name = :name, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $section->getName());
        $stmt->bindValue(':updated_at', $section->getUpdatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $section->getId());
        return $stmt->execute();
    }

    /**
     * Récupère toutes les sections
     *
     * @return Section[] Les sections
     */
    public function getAllSections(): array
    {
        $sql = "SELECT * FROM Section";
        $stmt = $this->db->query($sql);
        $sections = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sections[] = new Section(
                $row['id'],
                $row['name'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );
        }
        return $sections;
    }

    /**
     * Supprime une section
     *
     * @param string $id L'identifiant de la section
     */
    public function deleteSection(string $id): bool
    {
        $sql = "DELETE FROM Section WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
