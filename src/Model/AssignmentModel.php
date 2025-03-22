<?php

declare(strict_types=1);

namespace App\Model;

use PDO;
use App\Entity\Assignment;
use App\Entity\User;

class AssignmentModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
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
            $assignment = new Assignment(
                $row['id'],
                $row['name'],
                $row['description'],
                new \DateTime($row['start_period']),
                new \DateTime($row['end_period']),
                (int)$row['max_files'],
                (bool)$row['allow_late_submission'],
                isset($row['created_at']) ? new \DateTime($row['created_at']) : null,
                isset($row['updated_at']) ? new \DateTime($row['updated_at']) : null
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
