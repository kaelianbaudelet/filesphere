<?php
// src/Model/SchoolClassModel.php

declare(strict_types=1);

namespace App\Model;

use App\Entity\SchoolClass;
use App\Entity\User;
use App\Entity\Section;
use App\Entity\File;
use App\Entity\Assignment;

use PDO;
use Exception;

class SchoolClassModel
{
    private PDO $db;
    private $userModel;
    private $fileModel;

    public function __construct(PDO $db, $userModel = null, $fileModel = null)
    {
        $this->db = $db;
        $this->userModel = $userModel;
        $this->fileModel = $fileModel;
    }

    public function setUserModel($userModel): void
    {
        $this->userModel = $userModel;
    }

    public function setFileModel($fileModel): void
    {
        $this->fileModel = $fileModel;
    }

    public function createClass(SchoolClass $class): ?SchoolClass
    {
        try {
            // Démarrer la transaction
            $this->db->beginTransaction();

            // Insérer la classe
            $sql = "INSERT INTO Class (teacher_id, file_id, color, name) VALUES (:teacher_id, :file_id, :color, :name)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':teacher_id', $class->getTeacher()->getId());
            $stmt->bindValue(':file_id', $class->getIcon() ? $class->getIcon()->getId() : null);
            $stmt->bindValue(':color', $class->getColor());
            $stmt->bindValue(':name', $class->getName());

            $stmt->execute();

            // Récupérer l'UUID de la classe insérée
            $sql = "SELECT id FROM Class WHERE teacher_id = :teacher_id AND name = :name ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':teacher_id', $class->getTeacher()->getId());
            $stmt->bindValue(':name', $class->getName());
            $stmt->execute();
            $lastInsertedRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lastInsertedRow) {
                throw new Exception("Échec de la récupération de l'UUID de la classe.");
            }

            $classId = $lastInsertedRow['id'];
            // Insérer les étudiants
            $sql = "INSERT INTO ClassStudent (user_id, class_id) VALUES (:user_id, :class_id)";
            $stmt = $this->db->prepare($sql);
            foreach ($class->getStudents() as $student) {
                $stmt->bindValue(':user_id', $student->getId());
                $stmt->bindValue(':class_id', $classId);
                $stmt->execute();
            }

            // Valider la transaction
            $this->db->commit();

            return $this->getClassById($classId);
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            throw new Exception("Erreur lors de la création de la classe : " . $e->getMessage());
        }
    }

    public function uploadFilesAndAddToAssignment(array $files, string $assignmentId, string $userId): bool
    {
        try {
            $this->db->beginTransaction();
            $fileIds = [];

            // Vérifier la structure des fichiers
            $fileCount = is_array($files['name']) ? count($files['name']) : 0;

            // Préparer les requêtes SQL
            $sqlFile = "INSERT INTO File (token, name, extension, size, user_id) VALUES (:token, :name, :extension, :size, :user_id)";
            $stmtFile = $this->db->prepare($sqlFile);

            $sqlGetFileId = "SELECT id FROM File WHERE token = :token ORDER BY created_at DESC LIMIT 1";
            $stmtGetFileId = $this->db->prepare($sqlGetFileId);

            // Parcourir chaque fichier
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue; // Ignorer les fichiers avec erreur
                }

                $token = bin2hex(random_bytes(16));
                $fileName = $files['name'][$i];
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                // Insérer le fichier dans la base de données
                $stmtFile->bindValue(':name', $fileName);
                $stmtFile->bindValue(':token', $token);
                $stmtFile->bindValue(':extension', $extension);
                $stmtFile->bindValue(':size', $files['size'][$i]);
                $stmtFile->bindValue(':user_id', $userId);

                $stmtFile->execute();

                // Récupérer l'UUID du fichier inséré
                $stmtGetFileId->bindValue(':token', $token);
                $stmtGetFileId->execute();
                $lastInsertedRow = $stmtGetFileId->fetch(PDO::FETCH_ASSOC);

                if (!$lastInsertedRow) {
                    throw new Exception("Échec de la récupération de l'UUID du fichier.");
                }

                $fileIds[] = $lastInsertedRow['id'];

                // Déplacer le fichier téléchargé
                $uploadDir = __DIR__ . '/../../public/assets/upload/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                move_uploaded_file($files['tmp_name'][$i], $uploadDir . $token . '.' . $extension);
            }

            // Ajouter les relations assignmentFile
            if (!empty($fileIds)) {
                $sqlAssignmentFile = "INSERT INTO AssignmentFile (assignment_id, file_id, user_id) VALUES (:assignment_id, :file_id, :user_id)";
                $stmtAssignmentFile = $this->db->prepare($sqlAssignmentFile);

                foreach ($fileIds as $fileId) {
                    $stmtAssignmentFile->bindValue(':assignment_id', $assignmentId);
                    $stmtAssignmentFile->bindValue(':file_id', $fileId);
                    $stmtAssignmentFile->bindValue(':user_id', $userId);
                    $stmtAssignmentFile->execute();
                }
            }

            // Valider la transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            throw new Exception("Erreur lors de l'envoi des fichiers : " . $e->getMessage());
        }
    }

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

    //deleteFile

    public function deleteFile(string $fileId): bool
    {
        try {
            $this->db->beginTransaction();

            // Supprimer d'abord les relations devoir-fichier
            $sql = "DELETE FROM AssignmentFile WHERE file_id = :file_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':file_id', $fileId);
            $stmt->execute();

            // Supprimer ensuite le fichier
            $sql = "DELETE FROM File WHERE id = :file_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':file_id', $fileId);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la suppression du fichier : " . $e->getMessage());
        }
    }


    public function getClassById(string $id): ?SchoolClass
    {
        $sql = "SELECT * FROM Class WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $class = new SchoolClass(
            $row['id'],
            $this->getUserById($row['teacher_id']),
            $row['file_id'] ? $this->getFileById($row['file_id']) : null,
            (string)$row['color'],
            $row['name'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );

        // Récupérer les étudiants de cette classe
        $students = $this->getStudentsByClassId($row['id']);
        foreach ($students as $student) {
            $class->addStudent($student);
        }

        //sections
        $sections = $this->getSectionsByClassId($row['id']);
        foreach ($sections as $section) {
            $class->addSection($section);
        }

        return $class;
    }
    public function updateClass(SchoolClass $class): bool
    {
        try {
            $sql = "UPDATE Class SET teacher_id = :teacher_id, name = :name, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $class->getId());
            $stmt->bindValue(':teacher_id', $class->getTeacher()->getId());
            $stmt->bindValue(':name', $class->getName());
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de la classe : " . $e->getMessage());
        }
    }

    public function updateAssignment(assignment $assignment): bool
    {
        try {
            $sql = "UPDATE Assignment SET name = :name, description = :description, start_period = :start_period, end_period = :end_period, allow_late_submission = :allow_late_submission, max_files = :max_files, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $assignment->getId());
            $stmt->bindValue(':name', $assignment->getName());
            $stmt->bindValue(':description', $assignment->getDescription());
            $stmt->bindValue(':start_period', $assignment->getStartPeriod()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':end_period', $assignment->getEndPeriod()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':allow_late_submission', (int) $assignment->getAllowLateSubmission(), PDO::PARAM_INT);
            $stmt->bindValue(':max_files', $assignment->getMaxFiles());
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour du devoir : " . $e->getMessage());
        }
    }
    public function getAllClasses(string $userId): array
    {
        $user = $this->getUserById($userId);

        if ($user->getRole() === 'student') {
            $sql = "SELECT c.* FROM Class c
                JOIN ClassStudent cs ON c.id = cs.class_id
                WHERE cs.user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId);
        } elseif ($user->getRole() === 'teacher') {
            $sql = "SELECT * FROM Class WHERE teacher_id = :teacher_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':teacher_id', $userId);
        } else {
            $sql = "SELECT * FROM Class";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        $classes = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $class = new SchoolClass(
                $row['id'],
                $this->getUserById($row['teacher_id']),
                $row['file_id'] ? $this->getFileById($row['file_id']) : null,
                (string)$row['color'],
                (string)$row['name'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );

            // Récupérer les étudiants pour cette classe
            $students = $this->getStudentsByClassId($row['id']);
            foreach ($students as $student) {
                $class->addStudent($student);
            }

            // Récupérer les sections pour cette classe
            $sections = $this->getSectionsByClassId($row['id']);
            foreach ($sections as $section) {
                $class->addSection($section);
            }

            $classes[] = $class;
        }

        return $classes;
    }

    public function deleteClass(string $id): bool
    {
        try {
            $this->db->beginTransaction();

            // Supprimer d'abord les relations étudiant-classe
            $sql = "DELETE FROM ClassStudent WHERE class_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            // Supprimer ensuite la classe
            $sql = "DELETE FROM Class WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la suppression de la classe : " . $e->getMessage());
        }
    }

    public function deleteAssignment(string $assignmentId): bool
    {
        try {
            $this->db->beginTransaction();

            // Supprimer d'abord les relations section-devoir
            $sql = "DELETE FROM SectionAssignment WHERE assignment_id = :assignment_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':assignment_id', $assignmentId);
            $stmt->execute();

            // Supprimer ensuite le devoir
            $sql = "DELETE FROM Assignment WHERE id = :assignment_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':assignment_id', $assignmentId);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la suppression du devoir : " . $e->getMessage());
        }
    }

    public function getStudentsByClassId(string $classId): array
    {
        $sql = "SELECT u.* FROM User u
                JOIN ClassStudent cs ON u.id = cs.user_id
                WHERE cs.class_id = :class_id AND u.role = 'student'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':class_id', $classId);
        $stmt->execute();

        $students = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $students[] = new User(
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

        return $students;
    }

    public function getSectionsByClassId(string $classId): array
    {
        $sql = "SELECT s.* FROM Section s
                JOIN ClassSection cs ON s.id = cs.section_id
                WHERE cs.class_id = :class
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':class', $classId);
        $stmt->execute();

        $sections = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $section = new Section(
                $row['id'],
                $row['name'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );

            $assignments = $this->getAssignmentsBySectionId($row['id']);
            foreach ($assignments as $assignment) {
                $section->addAssignment($assignment);
            }

            $sections[] = $section;
        }

        return $sections;
    }
    // getassignmentsBySectionId (Sectionassignment join)
    public function getAssignmentsBySectionId(string $sectionId): array
    {
        $sql = "SELECT h.* FROM Assignment h
                JOIN SectionAssignment sh ON h.id = sh.assignment_id
                WHERE sh.section_id = :section_id
                ORDER BY h.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':section_id', $sectionId);
        $stmt->execute();

        $assignments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $assignment = new Assignment(
                $row['id'],
                $row['name'],
                $row['description'],
                new \DateTime($row['start_period']),
                new \DateTime($row['end_period']),
                $row['max_files'],
                (bool) $row['allow_late_submission'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );

            $instructionFiles = $this->getInstructionsFilesByAssignmentId($row['id']);
            foreach ($instructionFiles as $file) {
                $assignment->addInstructionFile($file);
            }

            $assignments[] = $assignment;
        }

        return $assignments;
    }


    public function getFilesByAssignmentId(string $assignmentId, string $userId): array
    {
        $user = $this->getUserById($userId);

        if ($user->getRole() === 'admin' || $user->getRole() === 'teacher') {
            $sql = "SELECT f.* FROM File f
                    JOIN AssignmentFile hf ON f.id = hf.file_id
                    WHERE hf.assignment_id = :assignment_id
                    ORDER BY f.created_at DESC";
        } else {
            $sql = "SELECT f.* FROM File f
                    WHERE f.id IN (
                        SELECT file_id FROM AssignmentFile WHERE assignment_id = :assignment_id
                    ) AND f.user_id = :user_id
                    ORDER BY f.created_at DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':assignment_id', $assignmentId);
        if ($user->getRole() !== 'admin' && $user->getRole() !== 'teacher') {
            $stmt->bindValue(':user_id', $userId);
        }
        $stmt->execute();

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


    public function getInstructionsFilesByAssignmentId(string $assignmentId): array
    {
        $sql = "SELECT f.* FROM File f
                JOIN AssignmentInstructionFile hf ON f.id = hf.file_id
                WHERE hf.assignment_id = :assignment_id
                ORDER BY f.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':assignment_id', $assignmentId);
        $stmt->execute();

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

    public function getUserById(string $id): ?User
    {
        // Si un UserModel a été injecté, l'utiliser
        if ($this->userModel) {
            return $this->userModel->getUserById($id);
        }

        // Sinon, faire la requête directement
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

    public function createSectionAndAddToClass(string $classId, Section $section): bool
    {
        try {
            // Démarrer la transaction
            $this->db->beginTransaction();

            // Insérer la section
            $sql = "INSERT INTO Section (name) VALUES (:name)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $section->getName());
            $stmt->execute();

            // Récupérer l'UUID de la section insérée
            $sql = "SELECT id FROM Section WHERE name = :name ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $section->getName());
            $stmt->execute();
            $lastInsertedRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lastInsertedRow) {
                throw new Exception("Échec de la récupération de l'UUID de la section.");
            }

            $sectionId = $lastInsertedRow['id'];

            // Ajouter la relation ClassSection
            $sql = "INSERT INTO ClassSection (section_id, class_id) VALUES (:section_id, :class_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':section_id', $sectionId);
            $stmt->bindValue(':class_id', $classId);
            $stmt->execute();

            // Valider la transaction
            $this->db->commit();

            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            throw new Exception("Erreur lors de l'ajout de la section à la classe : " . $e->getMessage());
        }
    }

    //createassignmentAndAddToSection
    public function createAssignmentAndAddToSection(string $sectionId, assignment $assignment, ?array $instructionsFiles = null, ?string $userId = null): bool
    {
        try {
            // Démarrer la transaction
            $this->db->beginTransaction();

            // Insérer le devoir
            $sql = "INSERT INTO Assignment (name, description, start_period, end_period, allow_late_submission, max_files) VALUES
(:name, :description, :start_period, :end_period, :allow_late_submission, :max_files)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $assignment->getName());
            $stmt->bindValue(':description', $assignment->getDescription());
            $stmt->bindValue(':start_period', $assignment->getStartPeriod()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':end_period', $assignment->getEndPeriod()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':allow_late_submission', (int) $assignment->getAllowLateSubmission(), PDO::PARAM_INT);
            $stmt->bindValue(':max_files', $assignment->getMaxFiles());
            $stmt->execute();

            // Récupérer l'UUID du devoir inséré
            $sql = "SELECT id FROM Assignment WHERE name = :name ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $assignment->getName());
            $stmt->execute();
            $lastInsertedRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lastInsertedRow) {
                throw new Exception("Échec de la récupération de l'UUID du devoir.");
            }

            $assignmentId = $lastInsertedRow['id'];

            // Ajouter la relation Sectionassignment
            $sql = "INSERT INTO SectionAssignment (section_id, assignment_id) VALUES (:section_id, :assignment_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':section_id', $sectionId);
            $stmt->bindValue(':assignment_id', $assignmentId);
            $stmt->execute();

            // Valider la transaction
            $this->db->commit();

            // si des fichiers d'instructions sont fournis, les télécharger et les ajouter au devoir
            if ($instructionsFiles) {
                $this->uploadInstructionFilesAndAddToAssignment($instructionsFiles, $assignmentId, $userId);
            }

            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            throw new Exception("Erreur lors de l'ajout du devoir à la section : " . $e->getMessage());
        }
    }

    public function uploadInstructionFilesAndAddToAssignment(array $files, string $assignmentId, string $userId): bool
    {
        try {
            $this->db->beginTransaction();
            $fileIds = [];

            // Vérifier la structure des fichiers
            $fileCount = is_array($files['name']) ? count($files['name']) : 0;

            // Préparer les requêtes SQL
            $sqlFile = "INSERT INTO File (token, name, extension, size, user_id) VALUES (:token, :name, :extension, :size, :user_id)";
            $stmtFile = $this->db->prepare($sqlFile);

            $sqlGetFileId = "SELECT id FROM File WHERE token = :token ORDER BY created_at DESC LIMIT 1";
            $stmtGetFileId = $this->db->prepare($sqlGetFileId);

            // Parcourir chaque fichier
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue; // Ignorer les fichiers avec erreur
                }

                $token = bin2hex(random_bytes(16));
                $fileName = $files['name'][$i];
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                // Insérer le fichier dans la base de données
                $stmtFile->bindValue(':name', $fileName);
                $stmtFile->bindValue(':token', $token);
                $stmtFile->bindValue(':extension', $extension);
                $stmtFile->bindValue(':size', $files['size'][$i]);
                $stmtFile->bindValue(':user_id', $userId);

                $stmtFile->execute();

                // Récupérer l'UUID du fichier inséré
                $stmtGetFileId->bindValue(':token', $token);
                $stmtGetFileId->execute();
                $lastInsertedRow = $stmtGetFileId->fetch(PDO::FETCH_ASSOC);

                if (!$lastInsertedRow) {
                    throw new Exception("Échec de la récupération de l'UUID du fichier.");
                }

                $fileIds[] = $lastInsertedRow['id'];

                // Déplacer le fichier téléchargé
                $uploadDir = __DIR__ . '/../../public/assets/upload/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                move_uploaded_file($files['tmp_name'][$i], $uploadDir . $token . '.' . $extension);
            }

            // Ajouter les relations assignmentFile
            if (!empty($fileIds)) {
                $sqlAssignmentFile = "INSERT INTO AssignmentInstructionFile (assignment_id, file_id) VALUES (:assignment_id, :file_id)";
                $stmtAssignmentFile = $this->db->prepare($sqlAssignmentFile);

                foreach ($fileIds as $fileId) {
                    $stmtAssignmentFile->bindValue(':assignment_id', $assignmentId);
                    $stmtAssignmentFile->bindValue(':file_id', $fileId);
                    $stmtAssignmentFile->execute();
                }
            }

            // Valider la transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            throw new Exception("Erreur lors de l'envoi des fichiers d'instructions : " . $e->getMessage());
        }
    }

    public function updateSection(Section $section): bool
    {
        try {
            $sql = "UPDATE Section SET name = :name, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $section->getId());
            $stmt->bindValue(':name', $section->getName());
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de la section : " . $e->getMessage());
        }
    }

    //getSectionById
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

        $section = new Section(
            $row['id'],
            $row['name'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );

        // Récupérer les devoirs de cette section
        $assignments = $this->getAssignmentsBySectionId($row['id']);
        foreach ($assignments as $assignment) {
            $section->addAssignment($assignment);
        }

        return $section;
    }

    //getassignmentById
    public function getAssignmentById(string $id, string $userId): ?assignment
    {
        $sql = "SELECT * FROM Assignment WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $assignment = new Assignment(
            $row['id'],
            $row['name'],
            $row['description'],
            new \DateTime($row['start_period']),
            new \DateTime($row['end_period']),
            $row['max_files'],
            (bool) $row['allow_late_submission'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );

        $files = $this->getFilesByAssignmentId($row['id'], $userId);
        foreach ($files as $file) {
            $assignment->addFile($file);
        }

        $instructionFiles = $this->getInstructionsFilesByAssignmentId($row['id']);
        foreach ($instructionFiles as $file) {
            $assignment->addInstructionFile($file);
        }

        return $assignment;
    }

    public function deleteSectionAndDeleteFromClass(string $classId, string $sectionId): bool
    {
        try {
            $this->db->beginTransaction();

            // Supprimer la relation ClassSection
            $sql = "DELETE FROM ClassSection WHERE class_id = :class_id AND section_id = :section_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':class_id', $classId);
            $stmt->bindValue(':section_id', $sectionId);
            $stmt->execute();

            // Supprimer la section
            $sql = "DELETE FROM Section WHERE id = :section_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':section_id', $sectionId);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la suppression de la section et de la relation : " . $e->getMessage());
        }
    }

    //deleteassignmentAndDeleteFromSection

    public function deleteAssignmentAndDeleteFromSection(string $sectionId, string $assignmentId): bool
    {
        try {
            $this->db->beginTransaction();

            // Supprimer la relation Sectionassignment
            $sql = "DELETE FROM SectionAssignment WHERE section_id = :section_id AND assignment_id = :assignment_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':section_id', $sectionId);
            $stmt->bindValue(':assignment_id', $assignmentId);
            $stmt->execute();

            // Supprimer le devoir
            $sql = "DELETE FROM Assignment WHERE id = :assignment_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':assignment_id', $assignmentId);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la suppression du devoir et de la relation : " . $e->getMessage());
        }
    }


    public function addStudentToClass(string $classId, string $studentId): bool
    {
        try {
            // Vérifier si la relation n'existe pas déjà
            $sql = "SELECT COUNT(*) as count FROM ClassStudent WHERE class_id = :class_id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':class_id', $classId);
            $stmt->bindValue(':user_id', $studentId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return true; // La relation existe déjà
            }

            // Ajouter la relation
            $sql = "INSERT INTO ClassStudent (user_id, class_id) VALUES (:user_id, :class_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $studentId);
            $stmt->bindValue(':class_id', $classId);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'ajout de l'étudiant à la classe : " . $e->getMessage());
        }
    }

    public function removeStudentFromClass(string $classId, string $studentId): bool
    {
        try {
            $sql = "DELETE FROM ClassStudent WHERE class_id = :class_id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':class_id', $classId);
            $stmt->bindValue(':user_id', $studentId);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression de l'étudiant de la classe : " . $e->getMessage());
        }
    }
}
