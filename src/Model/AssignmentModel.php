<?php
// src/Model/AssignmentModel.php

declare(strict_types=1);

namespace App\Model;

use PDO;
use App\Entity\Assignment;
use App\Entity\User;

/**
 * Modèle en charge de la gestion des devoirs
 */
class AssignmentModel
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
     * Récupère tous les devoirs d'un élève
     *
     * @param User $student L'élève
     * @return array<int, array<string, mixed>>
     */
    public function getAllAssignments(User $student): array
    {
        $stmt = $this->db->prepare('
        SELECT DISTINCT a.*, sa.section_id, cs.class_id
        FROM Assignment a
        JOIN SectionAssignment sa ON a.id = sa.assignment_id
        JOIN Section s ON sa.section_id = s.id
        JOIN ClassSection cs ON s.id = cs.section_id
        JOIN Class c ON cs.class_id = c.id
        JOIN ClassStudent cst ON c.id = cst.class_id
        WHERE cst.user_id = :studentId
        ');
        $studentId = $student->getId();
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_STR);
        $stmt->execute();
        $assignments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            /** @var array<string, mixed> $row */

            // Make sure we have valid values before creating the Assignment
            $id = isset($row['id']) ? (string)$row['id'] : '';
            $name = isset($row['name']) ? (string)$row['name'] : '';
            $description = isset($row['description']) ? (string)$row['description'] : '';
            $startPeriod = isset($row['start_period']) ? (string)$row['start_period'] : '';
            $endPeriod = isset($row['end_period']) ? (string)$row['end_period'] : '';
            $maxFiles = isset($row['max_files']) ? (int)$row['max_files'] : 0;
            $allowLateSubmission = isset($row['allow_late_submission']) ? (bool)$row['allow_late_submission'] : false;

            $assignment = new Assignment(
                $id,
                $name,
                $description,
                new \DateTime($startPeriod),
                new \DateTime($endPeriod),
                $maxFiles,
                $allowLateSubmission,
                isset($row['created_at']) ? new \DateTime((string)$row['created_at']) : null,
                isset($row['updated_at']) ? new \DateTime((string)$row['updated_at']) : null
            );

            $assignments[] = [
                'assignment' => $assignment,
                'class_id' => $row['class_id'],
                'section_id' => $row['section_id']
            ];
        }

        return $assignments;
    }
}
