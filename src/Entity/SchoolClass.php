<?php
// src/Entity/SchoolClass.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Entity\File;

use InvalidArgumentException;

class SchoolClass
{
    private ?string $id = null;
    private User $teacher;
    private ?File $icon;
    private string $color;
    private string $name;
    private ?\DateTime $created_at;
    private ?\DateTime $updated_at;
    public ?array $students = [];
    public ?array $sections = [];


    public function __construct(
        ?string $id,
        User $teacher,
        ?File $icon,
        string $color,
        string $name,
        ?\DateTime $created_at,
        ?\DateTime $updated_at
    ) {
        $this->id = $id;
        $this->teacher = $teacher;
        $this->icon = $icon;
        $this->color = $color;
        $this->name = $name;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getTeacher(): User
    {
        return $this->teacher;
    }

    public function setTeacherId(User $teacher): void
    {
        $this->teacher = $teacher;
    }

    public function getIcon(): ?File
    {
        return $this->icon;
    }

    public function setIcon(?File $icon): void
    {
        $this->icon = $icon;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    public function addStudent(User $student): void
    {
        $this->students[] = $student;
    }

    public function getStudents(): ?array
    {
        return $this->students;
    }

    public function getSections(): ?array
    {
        return $this->sections;
    }

    public function addSection(Section $section): void
    {
        $this->sections[] = $section;
    }
}
