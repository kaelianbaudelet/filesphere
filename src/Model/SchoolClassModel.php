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

/**
 * Modèle en charge de la gestion des classes
 */
class SchoolClassModel
{
    /**
     * @var PDO Instance de la classe PDO
     */
    private PDO $db;

    /**
     * @var UserModel|null Instance du modèle de gestion des utilisateurs
     */
    private $userModel;

    /**
     * @var FileModel|null Instance du modèle de gestion des fichiers
     */
    private $fileModel;

    public function __construct(PDO $db, $userModel = null, $fileModel = null)
    {
        $this->db = $db;
        $this->userModel = $userModel;
        $this->fileModel = $fileModel;
    }

    /**
     * Définit le modèle de gestion des utilisateurs
     *
     * @param UserModel $userModel Le modèle de gestion des utilisateurs
     */
    public function setUserModel($userModel): void
    {
        $this->userModel = $userModel;
    }

    /**
     * Définit le modèle de gestion des fichiers
     *
     * @param FileModel $fileModel Le modèle de gestion des fichiers
     */
    public function setFileModel($fileModel): void
    {
        $this->fileModel = $fileModel;
    }

    /**
     * Crée une classe
     *
     * @param SchoolClass $class La classe à créer
     */
    public function createClass(SchoolClass $class): ?SchoolClass
    {
        try {
            $this->db->beginTransaction();
            $sql = "INSERT INTO Class (teacher_id, file_id, color, name) VALUES (:teacher_id, :file_id, :color, :name)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':teacher_id', $class->getTeacher()->getId());
            $stmt->bindValue(':file_id', $class->getIcon() ? $class->getIcon()->getId() : null);
            $stmt->bindValue(':color', $class->getColor());
            $stmt->bindValue(':name', $class->getName());
            $stmt->execute();
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

            $sql = "INSERT INTO ClassStudent (user_id, class_id) VALUES (:user_id, :class_id)";
            $stmt = $this->db->prepare($sql);
            foreach ($class->getStudents() as $student) {
                $stmt->bindValue(':user_id', $student->getId());
                $stmt->bindValue(':class_id', $classId);
                $stmt->execute();
            }

            $this->db->commit();

            return $this->getClassById($classId);
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la création de la classe : " . $e->getMessage());
        }
    }

    /**
     * Télécharge les fichiers et les ajoute à un devoir
     *
     * @param array $files Les fichiers à télécharger
     * @param Assignment $assignment Le devoir
     * @param User $user L'utilisateur
     */
    public function uploadFilesAndAddToAssignment(array $files, Assignment $assignment, User $user): bool
    {
        try {
            $this->db->beginTransaction();
            $fileIds = [];

            $fileCount = is_array($files['name']) ? count($files['name']) : 0;
            $sqlFile = "INSERT INTO File (token, name, extension, size, user_id) VALUES (:token, :name, :extension, :size, :user_id)";
            $stmtFile = $this->db->prepare($sqlFile);

            $sqlGetFileId = "SELECT id FROM File WHERE token = :token ORDER BY created_at DESC LIMIT 1";
            $stmtGetFileId = $this->db->prepare($sqlGetFileId);

            for ($i = 0; $i < $fileCount; $i++) {

                $token = bin2hex(random_bytes(16));
                $fileName = $files['name'][$i];

                $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                $stmtFile->bindValue(':name', $fileName);
                $stmtFile->bindValue(':token', $token);
                $stmtFile->bindValue(':extension', $extension);
                $stmtFile->bindValue(':size', $files['size'][$i]);
                $stmtFile->bindValue(':user_id', $user->getId());
                $stmtFile->execute();
                $stmtGetFileId->bindValue(':token', $token);
                $stmtGetFileId->execute();
                $lastInsertedRow = $stmtGetFileId->fetch(PDO::FETCH_ASSOC);

                if (!$lastInsertedRow) {
                    throw new Exception("Échec de la récupération de l'UUID du fichier.");
                }

                $fileIds[] = $lastInsertedRow['id'];
                $uploadDir = __DIR__ . '/../../public/assets/upload/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                move_uploaded_file($files['tmp_name'][$i], $uploadDir . $token . '.' . $extension);
            }

            if (!empty($fileIds)) {
                $sqlAssignmentFile = "INSERT INTO AssignmentFile (assignment_id, file_id) VALUES (:assignment_id, :file_id)";
                $stmtAssignmentFile = $this->db->prepare($sqlAssignmentFile);

                foreach ($fileIds as $fileId) {
                    $stmtAssignmentFile->bindValue(':assignment_id', $assignment->getId());
                    $stmtAssignmentFile->bindValue(':file_id', $fileId);
                    $stmtAssignmentFile->execute();
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de l'envoi des fichiers : " . $e->getMessage());
        }
    }

    /**
     * Supprime les fichiers d'un devoir pour un utilisateur
     *
     * @param Assignment $assignment Le devoir
     * @param User $user L'utilisateur
     */
    public function deleteAssignmentFilesByUserId($assignment, $user): bool
    {
        try {
            $this->db->beginTransaction();
            $sqlGetFiles = "SELECT f.id, f.token, f.extension FROM File f
                            JOIN AssignmentFile af ON f.id = af.file_id
                            WHERE f.user_id = :user_id AND af.assignment_id = :assignment_id";
            $stmtGetFiles = $this->db->prepare($sqlGetFiles);
            $stmtGetFiles->bindValue(':user_id', $user->getId());
            $stmtGetFiles->bindValue(':assignment_id', $assignment->getId());
            $stmtGetFiles->execute();
            $files = $stmtGetFiles->fetchAll(PDO::FETCH_ASSOC);
            $fileIds = array_column($files, 'id');
            $uploadDir = __DIR__ . '/../../public/assets/upload/';
            foreach ($files as $file) {
                $filePath = $uploadDir . $file['token'] . '.' . $file['extension'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $sqlDeleteAssignmentFile = "DELETE FROM AssignmentFile
                                       WHERE assignment_id = :assignment_id
                                       AND file_id IN (SELECT id FROM File WHERE user_id = :user_id)";
            $stmtDeleteAssignmentFile = $this->db->prepare($sqlDeleteAssignmentFile);
            $stmtDeleteAssignmentFile->bindValue(':user_id', $user->getId());
            $stmtDeleteAssignmentFile->bindValue(':assignment_id', $assignment->getId());
            $stmtDeleteAssignmentFile->execute();

            if (!empty($fileIds)) {
                $placeholders = implode(',', array_fill(0, count($fileIds), '?'));
                $sqlDeleteFile = "DELETE FROM File WHERE id IN ($placeholders)";
                $stmtDeleteFile = $this->db->prepare($sqlDeleteFile);
                foreach ($fileIds as $i => $fileId) {
                    $stmtDeleteFile->bindValue($i + 1, $fileId);
                }
                $stmtDeleteFile->execute();
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error deleting assignment files: " . $e->getMessage());
        }
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
     * Récupère une classe par son identifiant
     *
     * @param string $id L'identifiant de la classe
     */
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

        $students = $this->getStudentsByClassId($row['id']);
        foreach ($students as $student) {
            $class->addStudent($student);
        }

        $sections = $this->getSectionsByClassId($row['id']);
        foreach ($sections as $section) {
            $class->addSection($section);
        }

        return $class;
    }

    /**
     * Met à jour une classe
     *
     * @param SchoolClass $class La classe à mettre à jour
     */
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

    /**
     * Met à jour un devoir
     *
     * @param Assignment $assignment Le devoir à mettre à jour
     */
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

    /**
     * Récupère toutes les classes
     *
     * @param string $userId L'identifiant de l'utilisateur
     */
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

            $students = $this->getStudentsByClassId($row['id']);
            foreach ($students as $student) {
                $class->addStudent($student);
            }

            $sections = $this->getSectionsByClassId($row['id']);
            foreach ($sections as $section) {
                $class->addSection($section);
            }

            $classes[] = $class;
        }

        return $classes;
    }

    /**
     * Supprime une classe
     *
     * @param string $id L'identifiant de la classe
     */
    public function deleteClass(string $id): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "DELETE FROM ClassStudent WHERE class_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

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

    /**
     * Supprime un devoir
     *
     * @param string $assignmentId L'identifiant du devoir
     */
    public function deleteAssignment(string $assignmentId): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "DELETE FROM SectionAssignment WHERE assignment_id = :assignment_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':assignment_id', $assignmentId);
            $stmt->execute();

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

    /**
     * Récupère les élèves d'une classe
     *
     * @param string $classId L'identifiant de la classe
     */
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

    /**
     * Récupère les sections d'une classe
     *
     * @param string $classId L'identifiant de la classe
     */
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

    /**
     * Récupère les devoirs d'une section
     *
     * @param string $sectionId L'identifiant de la section
     */
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

    /**
     * Récupère les fichiers d'un devoir
     *
     * @param string $assignmentId L'identifiant du devoir
     * @param User $user L'utilisateur
     */
    public function getFilesByAssignmentId(string $assignmentId, User $user): array
    {
        $user = $this->getUserById($user->getId());

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
            $stmt->bindValue(':user_id', $user->getId());
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

    /**
     * Récupère les fichiers d'instructions d'un devoir
     *
     * @param string $assignmentId L'identifiant du devoir
     */
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

    /**
     * Récupère un utilisateur par son identifiant
     *
     * @param string $id L'identifiant de l'utilisateur
     */
    public function getUserById(string $id): ?User
    {

        if ($this->userModel) {
            return $this->userModel->getUserById($id);
        }


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

    /**
     * Crée une section et l'ajoute à une classe
     *
     * @param string $classId L'identifiant de la classe
     * @param Section $section La section à créer
     */
    public function createSectionAndAddToClass(string $classId, Section $section): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO Section (name) VALUES (:name)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $section->getName());
            $stmt->execute();

            $sql = "SELECT id FROM Section WHERE name = :name ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $section->getName());
            $stmt->execute();
            $lastInsertedRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lastInsertedRow) {
                throw new Exception("Échec de la récupération de l'UUID de la section.");
            }

            $sectionId = $lastInsertedRow['id'];

            $sql = "INSERT INTO ClassSection (section_id, class_id) VALUES (:section_id, :class_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':section_id', $sectionId);
            $stmt->bindValue(':class_id', $classId);
            $stmt->execute();

            $this->db->commit();

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de l'ajout de la section à la classe : " . $e->getMessage());
        }
    }

    /**
     * Crée un devoir et l'ajoute à une section
     *
     * @param string $sectionId L'identifiant de la section
     * @param Assignment $assignment Le devoir à créer
     * @param array|null $instructionsFiles Les fichiers d'instructions
     * @param string|null $userId L'identifiant de l'utilisateur
     */
    public function createAssignmentAndAddToSection(string $sectionId, assignment $assignment, ?array $instructionsFiles = null, ?string $userId = null): bool
    {
        try {
            $this->db->beginTransaction();

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

            $sql = "SELECT id FROM Assignment WHERE name = :name ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $assignment->getName());
            $stmt->execute();
            $lastInsertedRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lastInsertedRow) {
                throw new Exception("Échec de la récupération de l'UUID du devoir.");
            }

            $assignmentId = $lastInsertedRow['id'];

            $sql = "INSERT INTO SectionAssignment (section_id, assignment_id) VALUES (:section_id, :assignment_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':section_id', $sectionId);
            $stmt->bindValue(':assignment_id', $assignmentId);
            $stmt->execute();

            $this->db->commit();

            if ($instructionsFiles) {
                $this->uploadInstructionFilesAndAddToAssignment($instructionsFiles, $assignmentId, $userId);
            }

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de l'ajout du devoir à la section : " . $e->getMessage());
        }
    }

    /**
     * Télécharger les fichiers d'instructions et les ajouter à un devoir
     *
     * @param array $files Les fichiers à télécharger
     * @param string $assignmentId L'identifiant du devoir
     * @param string $userId L'identifiant de l'utilisateur
     */
    public function uploadInstructionFilesAndAddToAssignment(array $files, string $assignmentId, string $userId): bool
    {
        try {
            $this->db->beginTransaction();
            $fileIds = [];

            $fileCount = is_array($files['name']) ? count($files['name']) : 0;

            $sqlFile = "INSERT INTO File (token, name, extension, size, user_id) VALUES (:token, :name, :extension, :size, :user_id)";
            $stmtFile = $this->db->prepare($sqlFile);

            $sqlGetFileId = "SELECT id FROM File WHERE token = :token ORDER BY created_at DESC LIMIT 1";
            $stmtGetFileId = $this->db->prepare($sqlGetFileId);

            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $token = bin2hex(random_bytes(16));
                $fileName = $files['name'][$i];

                $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                $stmtFile->bindValue(':name', $fileName);
                $stmtFile->bindValue(':token', $token);
                $stmtFile->bindValue(':extension', $extension);
                $stmtFile->bindValue(':size', $files['size'][$i]);
                $stmtFile->bindValue(':user_id', $userId);

                $stmtFile->execute();

                $stmtGetFileId->bindValue(':token', $token);
                $stmtGetFileId->execute();
                $lastInsertedRow = $stmtGetFileId->fetch(PDO::FETCH_ASSOC);

                if (!$lastInsertedRow) {
                    throw new Exception("Échec de la récupération de l'UUID du fichier.");
                }

                $fileIds[] = $lastInsertedRow['id'];

                $uploadDir = __DIR__ . '/../../public/assets/upload/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                move_uploaded_file($files['tmp_name'][$i], $uploadDir . $token . '.' . $extension);
            }

            if (!empty($fileIds)) {
                $sqlAssignmentFile = "INSERT INTO AssignmentInstructionFile (assignment_id, file_id) VALUES (:assignment_id, :file_id)";
                $stmtAssignmentFile = $this->db->prepare($sqlAssignmentFile);

                foreach ($fileIds as $fileId) {
                    $stmtAssignmentFile->bindValue(':assignment_id', $assignmentId);
                    $stmtAssignmentFile->bindValue(':file_id', $fileId);
                    $stmtAssignmentFile->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de l'envoi des fichiers d'instructions : " . $e->getMessage());
        }
    }

    /**
     * Met à jour une section
     *
     * @param Section $section La section à mettre à jour
     */
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

        return $section;
    }

    /**
     * Récupère un devoir par son identifiant
     *
     * @param string $id L'identifiant du devoir
     * @param User $user L'utilisateur
     */

    public function getAssignmentById(string $id, User $user): ?assignment
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

        $files = $this->getFilesByAssignmentId($row['id'], $user);
        foreach ($files as $file) {
            $assignment->addFile($file);
        }

        $instructionFiles = $this->getInstructionsFilesByAssignmentId($row['id']);
        foreach ($instructionFiles as $file) {
            $assignment->addInstructionFile($file);
        }

        return $assignment;
    }

    /**
     * Supprime une section d'une classe
     *
     * @param string $classId L'identifiant de la classe
     * @param string $sectionId L'identifiant de la section
     */
    public function deleteSectionAndDeleteFromClass(string $classId, string $sectionId): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "DELETE FROM ClassSection WHERE class_id = :class_id AND section_id = :section_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':class_id', $classId);
            $stmt->bindValue(':section_id', $sectionId);
            $stmt->execute();

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

    /**
     * Supprime un fichier
     *
     * @param string $fileId L'identifiant du fichier
     */
    public function deleteFile(string $fileId): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "SELECT * FROM File WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $fileId);
            $stmt->execute();
            $file = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$file) {
                throw new Exception("Fichier introuvable.");
            }

            $sql = "DELETE FROM File WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $fileId);
            $stmt->execute();

            $uploadDir = __DIR__ . '/../../public/assets/upload/';
            $filePath = $uploadDir . $file['token'] . '.' . $file['extension'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la suppression du fichier : " . $e->getMessage());
        }
    }

    /**
     * Supprime un devoir et le retire d'une section
     *
     * @param string $sectionId L'identifiant de la section
     * @param string $assignmentId L'identifiant du devoir
     */
    public function deleteAssignmentAndDeleteFromSection(string $sectionId, string $assignmentId): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "DELETE FROM SectionAssignment WHERE section_id = :section_id AND assignment_id = :assignment_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':section_id', $sectionId);
            $stmt->bindValue(':assignment_id', $assignmentId);
            $stmt->execute();

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

    /**
     * Ajoute un étudiant à une classe
     *
     * @param string $classId L'identifiant de la classe
     * @param string $studentId L'identifiant de l'étudiant
     */
    public function addStudentToClass(string $classId, string $studentId): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM ClassStudent WHERE class_id = :class_id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':class_id', $classId);
            $stmt->bindValue(':user_id', $studentId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return true;
            }

            $sql = "INSERT INTO ClassStudent (user_id, class_id) VALUES (:user_id, :class_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $studentId);
            $stmt->bindValue(':class_id', $classId);
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'ajout de l'étudiant à la classe : " . $e->getMessage());
        }
    }

    /**
     * Retire un étudiant d'une classe
     *
     * @param string $classId L'identifiant de la classe
     * @param string $studentId L'identifiant de l'étudiant
     */
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
