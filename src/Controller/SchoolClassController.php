<?php
// src/Controller/SchoolClassController.php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

use App\Entity\SchoolClass;
use App\Entity\Section;
use App\Entity\Assignment;
use App\Entity\File;


class SchoolClassController
{
    private $twig;
    private $schoolClassModel;
    private $userModel;
    private $fileModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->schoolClassModel = $dependencyContainer->get('SchoolClassModel');
        $this->userModel = $dependencyContainer->get('UserModel');
        $this->fileModel = $dependencyContainer->get('FileModel');

        $this->schoolClassModel->setUserModel($this->userModel);
        $this->schoolClassModel->setFileModel($this->fileModel);
    }

    public function classes()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $classes = $this->schoolClassModel->getAllClasses($_SESSION['user']['id']);
        $teachers = $this->userModel->getAllTeachers();
        $students = $this->userModel->getAllStudents();

        echo $this->twig->render('classController/classes.html.twig', [
            'classes' => $classes,
            'teachers' => $teachers,
            'students' => $students
        ]);
    }

    public function students(string $class_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }
        $class =  $this->schoolClassModel->getClassById($class_id);
        if (!$class) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Classe non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }
        $students = $this->userModel->getAllStudents();
        echo $this->twig->render('classController/classStudents.html.twig', [
            'class' => $class,
            'students' => $students
        ]);
    }

    // sections
    public function sections(string $class_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }
        $class =  $this->schoolClassModel->getClassById($class_id);
        if (!$class) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Classe non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }
        $sections = $this->schoolClassModel->getSectionsByClassId($class_id);
        echo $this->twig->render('classController/classSections.html.twig', [
            'class' => $class,
            'sections' => $sections
        ]);
    }

    public function assignmentDetails(string $class_id, string $section_id, string $assignment_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!$class_id || !$section_id || !$assignment_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe, de section ou de devoir manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $class = $this->schoolClassModel->getClassById($class_id);
        if (!$class) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Classe non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $section = $this->schoolClassModel->getSectionById($section_id);
        if (!$section) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Section non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user']['id']);
        if (!$user) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $assignment = $this->schoolClassModel->getAssignmentById($assignment_id, $user);

        if (!$assignment) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Devoir non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        echo $this->twig->render('classController/classSectionAssignmentDetails.html.twig', [
            'class' => $class,
            'section' => $section,
            'assignment' => $assignment
        ]);
    }


    // assignments
    public function assignments(string $class_id, string $section_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        if (!$class_id || !$section_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe ou de section manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }
        $class = $this->schoolClassModel->getClassById($class_id);
        if (!$class) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Classe non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }
        $section = $this->schoolClassModel->getSectionById($section_id);
        if (!$section) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Section non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }
        echo $this->twig->render('classController/classSectionAssignments.html.twig', [
            'class' => $class,
            'section' => $section,
        ]);
    }


    public function submissions(string $class_id, string $section_id, string $assignment_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!$class_id || !$section_id || !$assignment_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe, de section ou de devoir manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $class = $this->schoolClassModel->getClassById($class_id);
        if (!$class) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Classe non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $section = $this->schoolClassModel->getSectionById($section_id);
        if (!$section) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Section non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user']['id']);
        if (!$user) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $assignment = $this->schoolClassModel->getAssignmentById($assignment_id, $user);
        if (!$assignment) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Devoir non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        echo $this->twig->render('classController/classSectionAssignmentSubmissions.html.twig', [
            'class' => $class,
            'section' => $section,
            'assignment' => $assignment
        ]);
    }

    public function submitAssignment(string $class_id, string $section_id, string $assignment_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $_SESSION['user'] ?? null;

        if (!$class_id || !$section_id || !$assignment_id || !$user) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe, de section ou de devoir manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $class = $this->schoolClassModel->getClassById($class_id);
        if (!$class) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Classe non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $section = $this->schoolClassModel->getSectionById($section_id);
        if (!$section) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Section non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $user = $this->userModel->getUserById($user['id']);
        if (!$user) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $assignment = $this->schoolClassModel->getAssignmentById($assignment_id, $user);
        if (!$assignment) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Devoir non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/details#submit");
            exit;
        }

        $files = $_FILES['files'] ?? null;

        if (!$files) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Fichiers requis.',
                'context' => 'modal',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/details#submit");
            exit;
        }

        if ($assignment->getStartPeriod() > new \DateTime() || $assignment->getEndPeriod() < new \DateTime()) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Le devoir est terminé ou n\'a pas encore commencé. Vous ne pouvez pas télécharger de fichiers.',
                'context' => 'modal',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/details#submit");
            exit;
        }

        $files = $this->schoolClassModel->uploadFilesAndAddToAssignment($files, $assignment, $user);

        if ($files) {
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Fichiers téléchargés avec succès.',
                'submitted' => true,

            ];

            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/details#submitted");
            exit;
        } else {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors du téléchargement des fichiers.',
                'context' => 'modal',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/details#submit");
            exit;
        }

        header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/details#submitted");
        exit;
    }

    public function cancelSubmission(string $class_id, string $section_id, string $assignment_id)
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        // on verif que les param (slug) sont bien là
        $class = $this->schoolClassModel->getClassById($class_id);
        if (!$class) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Classe non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $section = $this->schoolClassModel->getSectionById($section_id);
        if (!$section) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Section non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        if (!isset($_POST['user_id'])) {
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/details");
            exit;
        }
        $user = $this->userModel->getUserById($_POST['user_id']);
        if (!$user) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $assignment = $this->schoolClassModel->getAssignmentById($assignment_id, $user);
        if (!$assignment) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Devoir non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $this->schoolClassModel->deleteAssignmentFilesByUserId($assignment, $user);

        $_SESSION['alert'] = [
            'status' => 'success',
            'message' => 'Fichier supprimé avec succès.',
        ];

        header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/details");
        exit;
    }

    public function downloadFile(string $class_id, string $section_id, string $assignment_id, string $file_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!$class_id || !$section_id || !$assignment_id || !$file_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe, de section, de devoir ou de fichier manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $file = $this->schoolClassModel->getFileById($file_id);
        if (!$file) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Fichier non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $students = $this->schoolClassModel->getStudentsByClassId($class_id);
        $class = $this->schoolClassModel->getClassById($class_id);

        if ($_SESSION['user']['role'] == 'student') {
            $student = $this->userModel->getUserById($_SESSION['user']['id']);
            if (
                $file->getOwner()->getId() != $_SESSION['user']['id'] &&
                ($file->getOwner()->getId() != $class->getTeacher()->getId() && !in_array($student, $students))
            ) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Vous n\'avez pas le droit de télécharger ce fichier. Vous n\'êtes pas l\'auteur du fichier ou vous n\'êtes pas dans la classe.',
                ];
                header('Location: /dashboard/classes/' . $class_id . '/sections/' . $section_id . '/assignments/' . $assignment_id . '/details');
                exit;
            }
        }

        $uploadDir = __DIR__ . '/../../public/assets/upload/';
        $filePath = $uploadDir . $file->getToken() . '.' . $file->getExtension();

        if (!file_exists($filePath)) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Fichier non trouvé.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file->getName() . '.' . $file->getExtension() . '"');
        readfile($filePath);
        exit;
    }

    public function createAssignment(string $class_id, string $section_id)
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            $class = $this->schoolClassModel->getClassById($class_id);
            if (!$class) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Classe non trouvée.',
                ];
                header('Location: /dashboard/classes');
                exit;
            }

            $section = $this->schoolClassModel->getSectionById($section_id);
            if (!$section) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Section non trouvée.',
                ];
                header('Location: /dashboard/classes');
                exit;
            }

            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments");
            exit;
        }

        if (!$class_id || !$section_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe ou de section manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;
        $startPeriod = $_POST['start_period'] ?? null;
        $endPeriod = $_POST['end_period'] ?? null;
        $maxFiles = $_POST['max_files'] ?? null;
        $allowLateSubmission = $_POST['allow_late_submission'] ?? false;
        $instructionFiles = $_FILES['instruction_files'] ?? [];

        if (!$name || !$description || !$startPeriod || !$endPeriod || !$maxFiles) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Les champs nom, description, période de début, période de fin et nombre de fichiers sont requis.',
                'context' => 'modal',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments#creation");
            exit;
        }

        if ($startPeriod >= $endPeriod) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La période de début doit être inférieure à la période de fin.',
                'context' => 'modal',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments#creation");
            exit;
        }

        if ($maxFiles < 1) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Le nombre de fichiers doit être supérieur à 0.',
                'context' => 'modal',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments");
            exit;
        }

        $assignment = new Assignment(
            null,
            $name,
            $description,
            new \DateTime($startPeriod),
            new \DateTime($endPeriod),
            (int)$maxFiles,
            (bool)$allowLateSubmission ?? false,
            null,
            null
        );

        try {
            $this->schoolClassModel->createAssignmentAndAddToSection($section_id, $assignment, $instructionFiles, $_SESSION['user']['id']);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Devoir ajouté à la section avec succès.',
            ];
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors de l\'ajout du devoir: ' . $e->getMessage(),
            ];
        }

        header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments");
        exit;
    }

    public function createSection(string $class_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        $name = $_POST['name'] ?? null;

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe requis.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        if (!$name) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Nom de section requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections");
            exit;
        }

        $class = $this->schoolClassModel->getClassById($class_id);
        if (!$class) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Classe non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        $section = new Section(null, $name, null, null);

        try {
            $this->schoolClassModel->createSectionAndAddToClass($class_id, $section);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Section ajoutée à la classe avec succès.',
            ];
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors de l\'ajout de la section: ' . $e->getMessage(),
            ];
        }

        header("Location: /dashboard/classes/{$class_id}/sections");
        exit;
    }

    //updateSection() method
    public function updateSection(string $class_id, string $section_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe requis.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        if (!$section_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de section requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections");
            exit;
        }

        // Affichage du formulaire pour l'édition
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $section = $this->schoolClassModel->getSectionById($section_id);
            if (!$section) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Section non trouvée.',
                ];
                header("Location: /dashboard/classes/{$class_id}/sections");
                exit;
            }

            $class = $this->schoolClassModel->getClassById($class_id);
            if (!$class) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Classe non trouvée.',
                ];
                header('Location: /dashboard/classes');
                exit;
            }

            echo $this->twig->render('classController/updateClassSection.html.twig', [
                'class' => $class,
                'section' => $section
            ]);
            return;
        }

        $name = $_POST['name'] ?? null;

        if (!$name) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Nom de section requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/edit");
            exit;
        }

        $section = new Section($section_id, $name, null, null);

        try {
            $this->schoolClassModel->updateSection($section);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Section mise à jour avec succès.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections");
            exit;
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la section: ' . $e->getMessage(),
            ];
        }

        header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/edit");
        exit;
    }

    public function updateAssignment(string $class_id, string $section_id, string $assignment_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if (!$class_id || !$section_id || !$assignment_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe, de section ou de devoir requis.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        // Affichage du formulaire pour l'édition
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            $user = $this->userModel->getUserById($_SESSION['user']['id']);
            if (!$user) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé.',
                ];
                header('Location: /dashboard/classes');
                exit;
            }

            $assignment = $this->schoolClassModel->getAssignmentById($assignment_id, $user);
            if (!$assignment) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Devoir non trouvé.',
                ];
                header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments");
                exit;
            }

            $class = $this->schoolClassModel->getClassById($class_id);
            if (!$class) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Classe non trouvée.',
                ];
                header('Location: /dashboard/classes');
                exit;
            }

            $section = $this->schoolClassModel->getSectionById($section_id);
            if (!$section) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Section non trouvée.',
                ];
                header("Location: /dashboard/classes/{$class_id}/sections");
                exit;
            }

            echo $this->twig->render('classController/updateClassSectionassignment.html.twig', [
                'class' => $class,
                'section' => $section,
                'assignment' => $assignment
            ]);
            return;
        }

        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;
        $startPeriod = $_POST['start_period'] ?? null;
        $endPeriod = $_POST['end_period'] ?? null;
        $allowLateSubmission = $_POST['allow_late_submission'] ?? false;
        $maxFiles = $_POST['max_files'] ?? null;

        if (!$name || !$description || !$startPeriod || !$endPeriod || !$maxFiles) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Les champs nom, description, période de début, période de fin et nombre de fichiers sont requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/edit");
            exit;
        }

        if ($startPeriod >= $endPeriod) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La période de début doit être inférieure à la période de fin.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/edit");
            exit;
        }

        $assignment = new Assignment(
            $assignment_id,
            $name,
            $description,
            new \DateTime($startPeriod),
            new \DateTime($endPeriod),
            (int)$maxFiles,
            (bool)$allowLateSubmission ?? false,
            null,
            null
        );

        try {
            $this->schoolClassModel->updateAssignment($assignment);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Devoir mis à jour avec succès.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments");
            exit;
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du devoir: ' . $e->getMessage(),
            ];
        }

        header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments/{$assignment_id}/edit");
        exit;
    }

    // addStudent() method
    public function addStudent(string $class_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }


        $students = $_POST['students'] ?? [];

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe requis.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        if (empty($students)) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Étudiants requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/students");
            exit;
        }

        foreach ($students as $studentId) {
            try {
                $this->schoolClassModel->addStudentToClass($class_id, $studentId);
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Erreur lors de l\'ajout de l\'étudiant: ' . $e->getMessage(),
                ];
                header("Location: /dashboard/classes/{$class_id}/students");
                exit;
            }
        }

        $_SESSION['alert'] = [
            'status' => 'success',
            'message' => 'Étudiants ajoutés à la classe avec succès.',
        ];
        header("Location: /dashboard/classes/{$class_id}/students");
        exit;
    }

    public function deleteStudent(string $class_id, string $student_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/classes');
            exit;
        }

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe requis.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        if (!$student_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID d\'étudiant requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/students");
            exit;
        }

        try {
            $this->schoolClassModel->removeStudentFromClass($class_id, $student_id);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Étudiant retiré de la classe avec succès.',
            ];
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors du retrait de l\'étudiant: ' . $e->getMessage(),
            ];
        }

        header("Location: /dashboard/classes/{$class_id}/students");
        exit;
    }

    // deleteassignment() method
    public function deleteAssignment(string $class_id, string $section_id, string $assignment_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }


        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/classes');
            exit;
        }

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe requis.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        if (!$section_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de section requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections");
            exit;
        }

        if (!$assignment_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de devoir requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments");
            exit;
        }

        try {
            $this->schoolClassModel->deleteAssignmentAndDeleteFromSection($section_id, $assignment_id);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Devoir supprimé avec succès.',
            ];
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du devoir: ' . $e->getMessage(),
            ];
        }

        header("Location: /dashboard/classes/{$class_id}/sections/{$section_id}/assignments");
        exit;
    }

    // deleteSection() method
    public function deleteSection(string $class_id, string $section_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/classes');
            exit;
        }

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe requis.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        if (!$section_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de section requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/sections");
            exit;
        }

        try {
            $this->schoolClassModel->deleteSectionAndDeleteFromClass($class_id, $section_id);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Section supprimée avec succès.',
            ];
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de la section: ' . $e->getMessage(),
            ];
        }

        header("Location: /dashboard/classes/{$class_id}/sections");
        exit;
    }
    public function createClass()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $teacherId = $_POST['teacher'] ?? null;
            $name = $_POST['name'] ?? null;
            $students = $_POST['students'] ?? [];
            $color = $_POST['color'] ?? null;
            $icon = $_FILES['icon'] ?? [];

            // Validation des données
            $errors = [];
            if (!$teacherId) {
                $errors[] = 'Le professeur est requis.';
            }
            if (!$name) {
                $errors[] = 'Le nom de la classe est requis.';
            }
            if (!$color) {
                $errors[] = 'La couleur est requise.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => implode(' ', $errors),
                ];
                header('Location: /dashboard/classes');
                exit;
            }

            $teacher = $this->userModel->getUserById($teacherId);
            if (!$teacher) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Professeur non trouvé.',
                ];
                header('Location: /dashboard/classes');
                exit;
            }

            $file = null;
            if ($icon && $icon['error'][0] === UPLOAD_ERR_OK) {
                $allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                ];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                $fileTmpName = $icon['tmp_name'][0];
                $fileError = $icon['error'][0];
                $fileName = 'class_icon_' . bin2hex(random_bytes(16)) . '.' . pathinfo($icon['name'][0], PATHINFO_EXTENSION);
                $fileType = mime_content_type($fileTmpName);
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (empty($fileTmpName) || $fileError !== UPLOAD_ERR_OK || !in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
                    $_SESSION['alert'] = [
                        'status' => 'error',
                        'message' => 'Erreur lors du téléchargement de l\'icône.',
                    ];
                    header('Location: /dashboard/classes');
                    exit;
                }

                $token = bin2hex(random_bytes(16));
                $newFileName = $token . '.' . $fileExtension;
                $uploadDir = __DIR__ . '/../../public/assets/upload/';
                $uploadFile = $uploadDir . $newFileName;

                if (!move_uploaded_file($fileTmpName, $uploadFile)) {
                    $_SESSION['alert'] = [
                        'status' => 'error',
                        'message' => 'Erreur lors du déplacement de l\'icône.',
                    ];
                    header('Location: /dashboard/classes');
                    exit;
                }

                $fileSize = filesize($uploadFile);
                $owner = $this->userModel->getUserById($_SESSION['user']['id']);
                $file = new File(null, $token, $fileName, $fileExtension, $fileSize, $owner, new \DateTime(), new \DateTime());
                $this->fileModel->createFile($file);

                $file = $this->fileModel->getFileByToken($token);
            }

            // Créer la classe
            $class = new SchoolClass(
                null,
                $teacher,
                $file,
                $color,
                $name,
                new \DateTime(),
                new \DateTime()
            );

            // Ajouter les étudiants
            foreach ($students as $studentId) {
                $student = $this->userModel->getUserById($studentId);
                if ($student) {
                    $class->addStudent($student);
                }
            }

            try {
                $this->schoolClassModel->createClass($class);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'Classe créée avec succès.',
                ];
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Erreur lors de la création de la classe: ' . $e->getMessage(),
                ];
            }

            header('Location: /dashboard/classes');
            exit;
        }

        header('Location: /dashboard/classes');
    }

    public function updateClass(string $class_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        // Affichage du formulaire pour l'édition
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $class = $this->schoolClassModel->getClassById($class_id);
            if (!$class) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Classe non trouvée.',
                ];
                header('Location: /dashboard/classes');
                exit;
            }

            $teachers = $this->userModel->getAllTeachers();
            $students = $this->userModel->getAllStudents();
            $classStudents = $this->schoolClassModel->getStudentsByClassId($class_id);

            // Préparation des IDs d'étudiants pour la sélection multiple dans le formulaire
            $selectedStudentIds = array_map(function ($student) {
                return $student->getId();
            }, $classStudents);

            echo $this->twig->render('classController/updateClass.html.twig', [
                'class' => $class,
                'teachers' => $teachers,
                'students' => $students,
                'selectedStudentIds' => $selectedStudentIds
            ]);
            return;
        }

        $teacherId = $_POST['teacher'] ?? null;
        $name = $_POST['name'] ?? null;

        if (!$name) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Nom classe requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/edit");
            exit;
        }

        if (!$teacherId) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Professeur requis.',
            ];
            header("Location: /dashboard/classes/{$class_id}/edit");
            exit;
        }

        $teacher = $this->userModel->getUserById($teacherId);
        if (!$teacher) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Professeur non trouvé.',
            ];
            header("Location: /dashboard/classes/{$class_id}/edit");
            exit;
        }

        $class = $this->schoolClassModel->getClassById($class_id);
        if (!$class) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Classe non trouvée.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }
        $class->setTeacherId($teacher);
        $class->setName($name);
        $class->setUpdatedAt(new \DateTime());

        try {
            $this->schoolClassModel->updateClass($class);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Classe mise à jour avec succès.',
            ];
            header('Location: /dashboard/classes');
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la classe: ' . $e->getMessage(),
            ];
            header("Location: /dashboard/classes/{$class_id}/edit");
        }
        exit;
    }

    public function deleteClass(string $class_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if (!$class_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'ID de classe manquant.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        try {
            $this->schoolClassModel->deleteClass($class_id);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Classe supprimée avec succès.',
            ];
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de la classe: ' . $e->getMessage(),
            ];
        }

        header('Location: /dashboard/classes');
        exit;
    }

    public function addStudentToClass()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard/classes');
            exit;
        }

        $classId = $_POST['class_id'] ?? null;
        $studentId = $_POST['student_id'] ?? null;

        if (!$classId || !$studentId) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'IDs de classe et d\'étudiant requis.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        try {
            $this->schoolClassModel->addStudentToClass($classId, $studentId);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Étudiant ajouté à la classe avec succès.',
            ];
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors de l\'ajout de l\'étudiant: ' . $e->getMessage(),
            ];
        }

        header('Location: /dashboard/classes');
        exit;
    }



    public function removeStudentFromClass()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'teacher') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        $classId = $_GET['class_id'] ?? null;
        $studentId = $_GET['student_id'] ?? null;

        if (!$classId || !$studentId) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'IDs de classe et d\'étudiant requis.',
            ];
            header('Location: /dashboard/classes');
            exit;
        }

        try {
            $this->schoolClassModel->removeStudentFromClass($classId, $studentId);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Étudiant retiré de la classe avec succès.',
            ];
        } catch (\Exception $e) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Erreur lors du retrait de l\'étudiant: ' . $e->getMessage(),
            ];
        }

        header('Location: /dashboard/classes');
        exit;
    }
}
