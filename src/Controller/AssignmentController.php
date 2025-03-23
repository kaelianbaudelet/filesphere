<?php
// src/Controller/AssignmentController.php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;

use Twig\Environment;

use App\Model\AssignmentModel;
use App\Model\UserModel;

/**
 * Class du contrôleur des devoirs
 */
class AssignmentController
{
    /**
     * @var Twig Instance de la classe Twig
     */
    private \Twig\Environment $twig;

    /**
     * @var AssignmentModel Instance de la classe AssignmentModel
     */
    private AssignmentModel $assignmentModel;

    /**
     * @var UserModel Instance de la classe UserModel
     */
    private UserModel $userModel;

    public function __construct(
        Environment $twig,
        DependencyContainer $dependencyContainer
    ) {
        $this->twig = $twig;
        $this->assignmentModel = $dependencyContainer->get('AssignmentModel');
        $this->userModel = $dependencyContainer->get('UserModel');
    }

    /**
     * Affiche la liste des devoirs de l'utilisateur connecté.
     *
     * @return void
     */
    public function assignments()
    {

        // Vérifie si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        // Récupère l'utilisateur connecté
        $user = $this->userModel->getUserById($_SESSION['user']['id']);

        // Vérifie si l'utilisateur existe
        if ($user === null) {
            header('Location: /');
        }

        // Vérifie si l'utilisateur est un étudiant
        if ($user->getRole() !== 'student') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        // Récupère les devoirs de l'utilisateur
        $studentAssignments = $this->assignmentModel->getAllAssignments($user);

        echo $this->twig->render('assignmentController/assignments.html.twig', ['studentAssignments' => $studentAssignments]);
    }
}
