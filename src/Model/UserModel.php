<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\User;
use PDO;

class UserModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createUser(User $user): bool
    {
        $sql = "INSERT INTO User (role, name, email, password, is_active, reset_token, reset_token_expires_at) VALUES
(:role, :name, :email, :password, :is_active, :reset_token, :reset_token_expires_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':name', $user->getName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':is_active', (bool)$user->getIsActive(), PDO::PARAM_INT);
        $stmt->bindValue(':reset_token', $user->getResetToken());
        $stmt->bindValue(':reset_token_expires_at', $user->getResetTokenExpiresAt()?->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }

    public function getUserByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM User WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
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

    public function updateUser(User $user): bool
    {
        $sql = "UPDATE User SET role = :role, name = :name, email = :email, password = :password, is_active = :is_active, reset_token = :reset_token, reset_token_expires_at = :reset_token_expires_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $user->getId());
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':name', $user->getName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':is_active', (bool)$user->getIsActive(), PDO::PARAM_INT);
        $stmt->bindValue(':reset_token', $user->getResetToken());
        $stmt->bindValue(':reset_token_expires_at', $user->getResetTokenExpiresAt()?->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }

    public function updateUserInformation(User $user): bool
    {
        $sql = "UPDATE User SET role = :role, name = :name, email = :email, is_active = :is_active, reset_token = :reset_token, reset_token_expires_at = :reset_token_expires_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $user->getId());
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':name', $user->getName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':is_active', (bool)$user->getIsActive(), PDO::PARAM_INT);
        $stmt->bindValue(':reset_token', $user->getResetToken());
        $stmt->bindValue(':reset_token_expires_at', $user->getResetTokenExpiresAt()?->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }

    public function getAllUsers(): array
    {
        $sql = "SELECT * FROM User";
        $stmt = $this->db->query($sql);
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
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
        return $users;
    }

    //get all student, teacher, admin

    public function getAllStudents(): array
    {
        $sql = "SELECT * FROM User WHERE role = 'student'";
        $stmt = $this->db->query($sql);
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
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
        return $users;
    }

    public function getAllTeachers(): array
    {
        $sql = "SELECT * FROM User WHERE role = 'teacher'";
        $stmt = $this->db->query($sql);
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
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
        return $users;
    }

    public function getAllAdmin(): array
    {
        $sql = "SELECT * FROM User WHERE role = 'admin'";
        $stmt = $this->db->query($sql);
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
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
        return $users;
    }



    public function deleteUser(User $user): bool
    {
        $sql = "DELETE FROM User WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $user->getId());
        return $stmt->execute();
    }

    public function resetPassword(User $user, string $password): bool
    {
        $sql = "UPDATE User SET password = :password, is_active = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $user->getId());
        $stmt->bindValue(':password', $password);
        return $stmt->execute();
    }

    public function updatePassword(User $user, string $password): bool
    {
        $sql = "UPDATE User SET password = :password, is_active = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $user->getId());
        $stmt->bindValue(':password', $password);
        return $stmt->execute();
    }

    // set reset token and reset token expires at
    public function setResetToken(User $user): bool
    {
        $sql = "UPDATE User
        SET reset_token = :reset_token, reset_token_expires_at = :reset_token_expires_at
        WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $user->getId());
        $stmt->bindValue(':reset_token', $user->getResetToken());
        $stmt->bindValue(':reset_token_expires_at', $user->getResetTokenExpiresAt()?->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }
}
