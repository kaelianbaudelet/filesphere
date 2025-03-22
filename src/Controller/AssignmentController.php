<?php
// src/Controller/SchoolClassController.php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

use App\Entity\Assignment;

class AssignmentController
{
    private $twig;
    private $assignmentModel;
    private $userModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->assignmentModel = $dependencyContainer->get('AssignmentModel');
        $this->userModel = $dependencyContainer->get('UserModel');
    }

    public function assignments()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user']['id']);
        if ($user->getRole() !== 'student') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        $studentAssignments = $this->assignmentModel->getAllAssignments($user);

        echo $this->twig->render('assignmentController/assignments.html.twig', ['studentAssignments' => $studentAssignments]);
    }
}
