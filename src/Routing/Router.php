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
    /** @var array<string, array{0: string, 1: string}> */
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
            '' => [DefaultController::class, 'home'],

            // Pages d'erreur
            '403' => [DefaultController::class, 'error403'],
            '404' => [DefaultController::class, 'error404'],
            '500' => [DefaultController::class, 'error500'],

            // Pages d'authentification
            'login' => [AuthController::class, 'login'],
            'register' => [AuthController::class, 'tempregister'],
            'logout' => [AuthController::class, 'logout'],
            'resetpassword' => [AuthController::class, 'resetPassword'],

            // Dashboard
            'dashboard' => [DashboardController::class, 'dashboard'],
            'dashboard/profile' => [DashboardController::class, 'profile'],
            'dashboard/profile/edit' => [DashboardController::class, 'updateProfile'],
            'dashboard/profile/editpassword' => [DashboardController::class, 'updatePassword'],

            // User management
            'dashboard/users' => [UserController::class, 'users'],
            'dashboard/users/create' => [UserController::class, 'createUser'],
            'dashboard/users/{user}/edit' => [UserController::class, 'updateUser'],
            'dashboard/users/{user}/delete' => [UserController::class, 'deleteUser'],
            'dashboard/users/{user}/resetpassword' => [UserController::class, 'resetPassword'],

            // File management
            'dashboard/files' => [FileController::class, 'files'],
            'dashboard/files/upload' => [FileController::class, 'uploadFile'],
            'dashboard/files/{file}/download' => [FileController::class, 'downloadFile'],
            'dashboard/files/{file}/delete' => [FileController::class, 'deleteFile'],

            // Assignments
            'dashboard/assignments' => [AssignmentController::class, 'assignments'],

            // Classes
            'dashboard/classes' => [SchoolClassController::class, 'classes'],
            'dashboard/classes/create' => [SchoolClassController::class, 'createClass'],
            'dashboard/classes/{class}/edit' => [SchoolClassController::class, 'updateClass'],
            'dashboard/classes/{class}/delete' => [SchoolClassController::class, 'deleteClass'],

            // Students
            'dashboard/classes/{class}/students' => [SchoolClassController::class, 'students'],
            'dashboard/classes/{class}/students/add' => [SchoolClassController::class, 'addStudent'],
            'dashboard/classes/{class}/students/{student}/delete' => [SchoolClassController::class, 'deleteStudent'],

            // Sections
            'dashboard/classes/{class}/sections' => [SchoolClassController::class, 'sections'],
            'dashboard/classes/{class}/sections/create' => [SchoolClassController::class, 'createSection'],
            'dashboard/classes/{class}/sections/{section}/edit' => [SchoolClassController::class, 'updateSection'],
            'dashboard/classes/{class}/sections/{section}/delete' => [SchoolClassController::class, 'deleteSection'],

            // Section assignments
            'dashboard/classes/{class}/sections/{section}/assignments' => [SchoolClassController::class, 'assignments'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/details' => [SchoolClassController::class, 'assignmentDetails'],
            'dashboard/classes/{class}/sections/{section}/assignments/create' => [SchoolClassController::class, 'createAssignment'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/edit' => [SchoolClassController::class, 'updateAssignment'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/delete' => [SchoolClassController::class, 'deleteAssignment'],

            // Submissions
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/submissions' => [SchoolClassController::class, 'submissions'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/submit' => [SchoolClassController::class, 'submitAssignment'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/submissions/cancel' => [SchoolClassController::class, 'cancelSubmission'],

            // Files
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/files/{file}/download' => [SchoolClassController::class, 'downloadFile'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/files/{file}/delete' => [SchoolClassController::class, 'deleteFile'],
        ];

        /**
         * @var array<string, array{0: class-string, 1: string}> $mappings
         */
        $this->pageMappings = $mappings;

        $this->defaultPage = '';
        $this->errorPage = '404';
    }

    /**
     * Route the request to the appropriate controller method
     *
     * @param mixed $twig The Twig environment instance
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
                [$controllerClass, $method] = $controllerInfo;

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
