<?php
// src/Routing/Router.php

declare(strict_types=1);

namespace App\Routing;

use App\Controller\DefaultController;
use App\Controller\AuthController;
use App\Controller\UserController;
use App\Controller\SchoolClassController;
use App\Controller\DashboardController;
use App\Controller\AssignmentController;
use App\Controller\FileController;
use App\Service\DependencyContainer;

class Router
{
    private DependencyContainer $dependencyContainer;
    /** @var array<string, array{0: string, 1: string, 2: array<string>}> */
    private array $pageMappings;
    private string $defaultPage;
    private string $errorPage;
    private ?string $currentRoute = null;

    public function __construct(DependencyContainer $dependencyContainer)
    {
        $this->dependencyContainer = $dependencyContainer;

        // Définir le tableau de mappages
        $mappings = [
            // Page d'accueil
            '' => [DefaultController::class, 'home', []],

            // Pages d'erreur
            '403' => [DefaultController::class, 'error403', []],
            '404' => [DefaultController::class, 'error404', []],
            '500' => [DefaultController::class, 'error500', []],

            // Pages d'authentification
            'login' => [AuthController::class, 'login', []],
            'register' => [AuthController::class, 'tempregister', []],
            'logout' => [AuthController::class, 'logout', []],
            'resetpassword' => [AuthController::class, 'resetPassword', []],

            // Dashboard
            'dashboard' => [DashboardController::class, 'dashboard', ['admin', 'teacher', 'student']],
            'dashboard/profile' => [DashboardController::class, 'profile', ['admin', 'teacher', 'student']],
            'dashboard/profile/edit' => [DashboardController::class, 'updateProfile', ['admin', 'teacher', 'student']],
            'dashboard/profile/editpassword' => [DashboardController::class, 'updatePassword', ['admin', 'teacher', 'student']],

            // Utilisateurs
            'dashboard/users' => [UserController::class, 'users', ['admin']],
            'dashboard/users/create' => [UserController::class, 'createUser', ['admin']],
            'dashboard/users/{user}/edit' => [UserController::class, 'updateUser', ['admin']],
            'dashboard/users/{user}/delete' => [UserController::class, 'deleteUser', ['admin']],
            'dashboard/users/{user}/resetpassword' => [UserController::class, 'resetPassword', ['admin']],

            // Fichiers
            'dashboard/files' => [FileController::class, 'files', ['admin']],
            'dashboard/files/upload' => [FileController::class, 'uploadFile', ['admin']],
            'dashboard/files/{file}/download' => [FileController::class, 'downloadFile', ['admin']],
            'dashboard/files/{file}/delete' => [FileController::class, 'deleteFile', ['admin']],

            // Devoirs de l'etudiant
            'dashboard/assignments' => [AssignmentController::class, 'assignments', ['student']],

            // Classes
            'dashboard/classes' => [SchoolClassController::class, 'classes', ['admin', 'teacher', 'student']],
            'dashboard/classes/create' => [SchoolClassController::class, 'createClass', ['admin']],
            'dashboard/classes/{class}/edit' => [SchoolClassController::class, 'updateClass', ['admin']],
            'dashboard/classes/{class}/delete' => [SchoolClassController::class, 'deleteClass', ['admin']],

            // Etudiants
            'dashboard/classes/{class}/students' => [SchoolClassController::class, 'students', ['admin']],
            'dashboard/classes/{class}/students/add' => [SchoolClassController::class, 'addStudent', ['admin']],
            'dashboard/classes/{class}/students/{student}/delete' => [SchoolClassController::class, 'deleteStudent', ['admin']],

            // Sections
            'dashboard/classes/{class}/sections' => [SchoolClassController::class, 'sections', ['admin', 'teacher', 'student']],
            'dashboard/classes/{class}/sections/create' => [SchoolClassController::class, 'createSection', ['admin', 'teacher']],
            'dashboard/classes/{class}/sections/{section}/edit' => [SchoolClassController::class, 'updateSection', ['admin', 'teacher']],
            'dashboard/classes/{class}/sections/{section}/delete' => [SchoolClassController::class, 'deleteSection', ['admin', 'teacher']],

            // Devoirs de la classe
            'dashboard/classes/{class}/sections/{section}/assignments' => [SchoolClassController::class, 'assignments', ['admin', 'teacher', 'student']],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/details' => [SchoolClassController::class, 'assignmentDetails', ['admin', 'teacher', 'student']],
            'dashboard/classes/{class}/sections/{section}/assignments/create' => [SchoolClassController::class, 'createAssignment', ['admin', 'teacher']],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/edit' => [SchoolClassController::class, 'updateAssignment', ['admin', 'teacher']],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/delete' => [SchoolClassController::class, 'deleteAssignment', ['admin', 'teacher']],

            // Soumissions
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/submissions' => [SchoolClassController::class, 'submissions', ['admin', 'teacher', 'student']],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/submit' => [SchoolClassController::class, 'submitAssignment', ['student']],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/submissions/cancel' => [SchoolClassController::class, 'cancelSubmission', ['student']],

            // Fichiers des devoirs
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/files/{file}/download' => [SchoolClassController::class, 'downloadFile', ['admin', 'teacher', 'student']],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/files/{file}/delete' => [SchoolClassController::class, 'deleteFile', ['admin', 'teacher']],
        ];

        /**
         * @var array<string, array{0: class-string, 1: string, 2: array<string>}> $mappings
         */
        $this->pageMappings = $mappings;

        $this->defaultPage = '';
        $this->errorPage = '404';
    }
    /**
     * Regarde si l'utilisateur a la permission d'acceder a la page
     *
     * @param array<string> $allowedRoles Les roles autorises a acceder a la page
     * @return bool True si l'utilisateur a la permission, sinon False
     */
    private function checkPermission(array $allowedRoles): bool
    {
        // Si aucun role n'est specifie, tout le monde a la permission
        if (empty($allowedRoles)) {
            return true;
        }

        // Si l'utilisateur n'est pas connecte, il n'a pas la permission
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
            return false;
        }

        $userRole = $_SESSION['user']['role'];

        // Verifiez si le role de l'utilisateur est dans le tableau des roles autorises
        return in_array($userRole, $allowedRoles, true);
    }

    /**
     * Route qui demande le controleur approprie
     *
     * @param mixed $twig Instance de la classe Twig
     * @return void
     */
    public function route($twig): void
    {
        $requestedPage = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($requestedPage === null || $requestedPage === false) {
            $requestedPage = $this->defaultPage;
        }

        $this->currentRoute = $requestedPage;
        $params = [];

        foreach ($this->pageMappings as $routePattern => $controllerInfo) {
            // Assurez-vous que $routePattern est une chaîne
            $routePatternStr = (string)$routePattern;

            $regexPattern = preg_replace('/\{[^\/]+\}/', '([^\/]+)', $routePatternStr);
            $regexPattern = "#^" . $regexPattern . "$#";

            if (preg_match($regexPattern, $requestedPage, $matches)) {
                array_shift($matches);
                $params = $matches;
                [$controllerClass, $method, $allowedRoles] = $controllerInfo;

                // On verifie si l'utilisateur a la permission d'acceder a la page
                if (!$this->checkPermission($allowedRoles)) {

                    // Si l'utilisateur n'est pas connecte, redirigez-le vers la page de connexion
                    if (!isset($_SESSION['user'])) {
                        header('Location: /login');
                        exit;
                    }

                    $forbiddenInfo = $this->pageMappings['403'];
                    [$errorControllerClass, $errorMethod] = $forbiddenInfo;
                    $errorController = new $errorControllerClass($twig, $this->dependencyContainer);
                    /** @var callable $callback */
                    $callback = [$errorController, $errorMethod];
                    call_user_func($callback);
                    return;
                }

                if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
                    $controller = new $controllerClass($twig, $this->dependencyContainer);
                    /** @var callable $callback */
                    $callback = [$controller, $method];
                    call_user_func_array($callback, $params);
                    return;
                }
            }
        }

        $error404Info = $this->pageMappings[$this->errorPage];
        [$errorControllerClass, $errorMethod] = $error404Info;
        $errorController = new $errorControllerClass($twig, $this->dependencyContainer);
        /** @var callable $callback */
        $callback = [$errorController, $errorMethod];
        call_user_func($callback);
    }

    public function getCurrentRoute(): ?string
    {
        return $this->currentRoute;
    }
}
