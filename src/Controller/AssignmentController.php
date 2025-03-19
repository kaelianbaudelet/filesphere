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

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->assignmentModel = $dependencyContainer->get('AssignmentModel');
    }

    public function assignments()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $assignments = $this->assignmentModel->getAllAssignments($_SESSION['user']['id']);

        echo $this->twig->render('assignmentController/assignments.html.twig', ['assignments' => $assignments]);
    }
}
