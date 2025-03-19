<?php

declare(strict_types=1);

namespace App\Model;

use PDO;
use App\Entity\Assignment;

class AssignmentModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllAssignments(string $studentId): array
    {
        $stmt = $this->db->prepare('
            SELECT a.*
            FROM Assignment a
            JOIN Section s ON a.id = s.id
            JOIN Class c ON s.id = c.id
            JOIN ClassStudent cs ON c.id = cs.user_id
            WHERE cs.user_id = :studentId
        ');
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_STR);
        $stmt->execute();

        $assignments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $assignments[] = new Assignment(
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
        }

        return $assignments;
    }
}
