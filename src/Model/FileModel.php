<?php
// src/Model/FileModel.php

declare(strict_types=1);

namespace App\Model;

use App\Entity\File;
use App\Entity\User;
use PDO;

/**
 * Modèle en charge de la gestion des fichiers
 */
class FileModel
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
     * Crée un fichier
     *
     * @param File $file Le fichier à créer
     */
    public function createFile(File $file): bool
    {
        $sql = "INSERT INTO File (token, name, extension, size, user_id, created_at, updated_at) VALUES
(:token, :name, :extension, :size, :user_id, :created_at, :updated_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $file->getToken());
        $stmt->bindValue(':name', $file->getName());
        $stmt->bindValue(':extension', $file->getExtension());
        $stmt->bindValue(':size', $file->getSize());
        $stmt->bindValue(':user_id', $file->getOwner()->getId());
        $stmt->bindValue(':created_at', $file->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $file->getUpdatedAt()->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }

    /**
     * Récupère un fichier par son identifiant
     *
     * @param string $id L'identifiant du fichier
     */
    public function getFileById(string $id): ?File
    {
        $sql = "SELECT * FROM File WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new File(
            $row['id'],
            $row['token'],
            $row['name'],
            $row['extension'],
            $row['size'],
            $this->getUserById($row['user_id']),
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }

    /**
     * Met à jour un fichier
     *
     * @param File $file Le fichier à mettre à jour
     */
    public function updateFile(File $file): bool
    {
        $sql = "UPDATE File SET name = :name, extension = :extension, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $file->getName());
        $stmt->bindValue(':extension', $file->getExtension());
        $stmt->bindValue(':updated_at', $file->getUpdatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $file->getId());
        return $stmt->execute();
    }

    /**
     * Récupère tous les fichiers
     *
     * @return File[] La liste des fichiers
     */
    public function getAllFiles(): array
    {
        $sql = "SELECT * FROM File";
        $stmt = $this->db->query($sql);
        $files = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $files[] = new File(
                $row['id'],
                $row['token'],
                $row['name'],
                $row['extension'],
                $row['size'],
                $this->getUserById($row['user_id']),
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );
        }
        return $files;
    }

    /**
     * Récupère un fichier par son token
     *
     * @param string $token Le token du fichier
     */
    public function getFileByToken(string $token): ?File
    {
        $sql = "SELECT * FROM File WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new File(
            $row['id'],
            $row['token'],
            $row['name'],
            $row['extension'],
            $row['size'],
            $this->getUserById($row['user_id']),
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }

    /**
     * Supprime un fichier
     *
     * @param string $id L'identifiant du fichier
     */
    public function deleteFile(string $id): bool
    {
        $sql = "DELETE FROM File WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    /**
     * Récupère un utilisateur par son identifiant
     *
     * @param string $id L'identifiant de l'utilisateur
     */
    public function getUserById(string $id): ?User
    {
        $sql = "SELECT * FROM User WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new User(
            $row['id'],
            $row['role'],
            $row['name'],
            $row['email'],
            $row['password'],
            (bool)$row['is_active'],
            $row['reset_token'],
            $row['reset_token_expires_at'] ? new \DateTime($row['reset_token_expires_at']) : null,
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }
}
