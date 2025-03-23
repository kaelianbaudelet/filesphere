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

/**
 * Routeur de l'application
 */
class Router
{
    /**
     * @var DependencyContainer Instance du conteneur de dépendances
     */
    private DependencyContainer $dependencyContainer;

    /**
     * @var array Tableau associatif contenant les routes et les contrôleurs associés
     */
    private array $pageMappings;

    /**
     * @var string Page par défaut
     */
    private string $defaultPage;

    /**
     * @var string Page d'erreur
     */
    private string $errorPage;

    /**
     * @var string Route actuelle
     */
    private ?string $currentRoute = null;

    public function __construct(DependencyContainer $dependencyContainer)
    {
        $this->dependencyContainer = $dependencyContainer;

        $this->pageMappings = [
            '' => [DefaultController::class, 'home'],

            '403' => [DefaultController::class, 'error403'],
            '404' => [DefaultController::class, 'error404'],
            '500' => [DefaultController::class, 'error500'],

            'login' => [AuthController::class, 'login'],
            'register' => [AuthController::class, 'tempregister'],
            'logout' => [AuthController::class, 'logout'],
            'resetpassword' => [AuthController::class, 'resetPassword'],

            'dashboard' => [DashboardController::class, 'dashboard'],
            'dashboard/profile' => [DashboardController::class, 'profile'],
            'dashboard/profile/edit' => [DashboardController::class, 'updateProfile'],
            'dashboard/profile/editpassword' => [DashboardController::class, 'updatePassword'],

            'dashboard/users' => [UserController::class, 'users'],
            'dashboard/users/create' => [UserController::class, 'createUser'],
            'dashboard/users/{user}/edit' => [UserController::class, 'updateUser'],
            'dashboard/users/{user}/delete' => [UserController::class, 'deleteUser'],
            'dashboard/users/{user}/resetpassword' => [UserController::class, 'resetPassword'],

            'dashboard/files' => [FileController::class, 'files'],
            'dashboard/files/upload' => [FileController::class, 'uploadFile'],
            'dashboard/files/{file}/download' => [FileController::class, 'downloadFile'],
            'dashboard/files/{file}/delete' => [FileController::class, 'deleteFile'],

            'dashboard/assignments' => [AssignmentController::class, 'assignments'],

            'dashboard/classes' => [SchoolClassController::class, 'classes'],
            'dashboard/classes/create' => [SchoolClassController::class, 'createClass'],
            'dashboard/classes/{class}/edit' => [SchoolClassController::class, 'updateClass'],
            'dashboard/classes/{class}/delete' => [SchoolClassController::class, 'deleteClass'],

            'dashboard/classes/{class}/students' => [SchoolClassController::class, 'students'],
            'dashboard/classes/{class}/students/add' => [SchoolClassController::class, 'addStudent'],
            'dashboard/classes/{class}/students/{student}/delete' => [SchoolClassController::class, 'deleteStudent'],

            'dashboard/classes/{class}/sections' => [SchoolClassController::class, 'sections'],
            'dashboard/classes/{class}/sections/create' => [SchoolClassController::class, 'createSection'],
            'dashboard/classes/{class}/sections/{section}/edit' => [SchoolClassController::class, 'updateSection'],
            'dashboard/classes/{class}/sections/{section}/delete' => [SchoolClassController::class, 'deleteSection'],

            'dashboard/classes/{class}/sections/{section}/assignments' => [SchoolClassController::class, 'assignments'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/details' => [SchoolClassController::class, 'assignmentDetails'],
            'dashboard/classes/{class}/sections/{section}/assignments/create' => [SchoolClassController::class, 'createAssignment'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/edit' => [SchoolClassController::class, 'updateAssignment'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/delete' => [SchoolClassController::class, 'deleteAssignment'],

            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/submissions' => [SchoolClassController::class, 'submissions'],

            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/submit' => [SchoolClassController::class, 'submitAssignment'],

            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/submissions/cancel' => [SchoolClassController::class, 'cancelSubmission'],

            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/files/{file}/download' => [SchoolClassController::class, 'downloadFile'],
            'dashboard/classes/{class}/sections/{section}/assignments/{assignment}/files/{file}/delete' => [SchoolClassController::class, 'deleteFile'],
        ];

        $this->defaultPage = '';
        $this->errorPage = '404';
    }

    /**
     * Route la requête vers le contrôleur approprié.
     *
     * @param \Twig\Environment $twig Instance de la classe Twig
     * @return void
     */
    public function route(\Twig\Environment $twig): void
    {
        $requestedPage = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

        if (!$requestedPage) {
            $requestedPage = $this->defaultPage;
        }

        $this->currentRoute = $requestedPage;
        $params = [];

        foreach ($this->pageMappings as $routePattern => $controllerInfo) {
            if (!is_string($routePattern) || !is_array($controllerInfo)) {
                continue;
            }
            if (!is_string($routePattern)) {
                continue;
            }

            $regexPattern = "#^" . preg_quote($routePattern, '#') . "$#";
            if (preg_match($regexPattern, $requestedPage, $matches) === 1) {

                [$controllerClass, $method] = $controllerInfo;
                if (!is_string($controllerClass) || !is_string($method)) {
                    continue;
                }
                array_shift($matches);
                $params = $matches;
                [$controllerClass, $method] = $controllerInfo;

                if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
                    $controller = new $controllerClass($twig, $this->dependencyContainer);
                    call_user_func_array([$controller, $method], $params);
                    return;
                }
            }
        }

        $error404Info = $this->pageMappings[$this->errorPage];
        [$errorControllerClass, $errorMethod] = $error404Info;
        $errorController = new $errorControllerClass($twig, $this->dependencyContainer);
        call_user_func([$errorController, $errorMethod]);
    }

    /**
     * Récupère la route actuelle.
     *
     * @return string|null La route actuelle
     */
    public function getCurrentRoute(): ?string
    {
        return $this->currentRoute;
    }
}
