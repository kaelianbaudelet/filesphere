<?php

declare(strict_types=1);

namespace App\Controller;


use App\Service\DependencyContainer;
use Twig\Environment;

class DashboardController
{
    private $twig;
    private $userModel;
    private $schoolClassModel;
    private $assignmentModel;
    private $fileModel;


    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->userModel = $dependencyContainer->get('UserModel');
        $this->schoolClassModel = $dependencyContainer->get('SchoolClassModel');
        $this->assignmentModel = $dependencyContainer->get('AssignmentModel');
        $this->fileModel = $dependencyContainer->get('FileModel');
    }

    public function dashboard()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user']['id']);
        if (!$user) {
            unset($_SESSION['user']);
            header('Location: /login');
            exit;
        }

        $classes = $this->schoolClassModel->getAllClasses($user->getId());
        $assignments = $this->assignmentModel->getAllAssignments($user);
        $files = $this->fileModel->getAllFiles($user->getId());
        $users = $this->userModel->getAllUsers();

        echo $this->twig->render('dashboardController/dashboard.html.twig', ['classes' => $classes, 'assignments' => $assignments, 'files' => $files, 'users' => $users]);
    }

    //profile
    public function profile()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user']['id']);

        echo $this->twig->render('dashboardController/profile.html.twig', ['user' => $user]);
    }

    public function updateProfile()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];

            if (!$name || !$email) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Tous les champs sont obligatoires.',
                ];
                header('Location: /dashboard/profile/edit');
                exit;
            }

            $user = $_SESSION['user'];
            $this->userModel->updateProfile($user['id'], $name, $email);

            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Profil modifié avec succès.',
            ];
            header('Location: /dashboard/profile/edit');
            exit;
        }

        echo $this->twig->render('dashboardController/updateProfile.html.twig', []);
    }

    public function updatePassword()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'];
            $confirmNewPassword = $_POST['confirm_new_password'];

            if (!$newPassword || !$confirmNewPassword) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Tous les champs sont obligatoires.',
                ];
                header('Location: /dashboard/profile/editpassword');
                exit;
            }

            if ($newPassword !== $confirmNewPassword) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Les mots de passe ne correspondent pas.',
                ];
                header('Location: /dashboard/profile/editpassword');
                exit;
            }

            $user = $this->userModel->getUserById($_SESSION['user']['id']);

            $this->userModel->updatePassword($user, password_hash($newPassword, PASSWORD_DEFAULT));

            unset($_SESSION['user']);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Mot de passe modifié avec succès, veuillez vous reconnecter avec votre nouveau mot de passe.',
            ];

            header('Location: /login');
            exit;
        }
        echo $this->twig->render('dashboardController/profile.html.twig', []);
    }
}
