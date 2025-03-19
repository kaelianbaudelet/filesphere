<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;
use App\Entity\User;
use App\Service\Mailer;

class UserController
{
    private $twig;
    private $userModel;
    private $mailer;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->userModel = $dependencyContainer->get('UserModel');
        $this->mailer = new Mailer($twig);
    }

    public function users()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        $users = $this->userModel->getAllUsers();
        echo $this->twig->render('userController/users.html.twig', ['users' => $users]);
    }

    public function createUser()
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $type = $_POST['role'];
            $name = $_POST['name'];

            if (!$email || !$type || !$name) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email, type et nom sont obligatoires.',
                    'context' => 'popup',
                ];
                header('Location: /dashboard/users#create');
                exit;
            }

            if ($type != 'student' && $type != 'teacher' && $type != 'admin') {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Type incorrect.',
                    'context' => 'popup',
                ];
                header('Location: /dashboard/users#create');
                exit;
            }

            $user = $this->userModel->getUserByEmail($email);

            if ($user) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email déjà utilisé.',
                    'context' => 'popup',
                ];
                header('Location: /dashboard/users#create');
                exit;
            }

            $password = bin2hex(random_bytes(12));
            $user = new User(
                null,
                $type,
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                null,
                null,
                null,
                null,
                null,
            );

            $this->userModel->createUser($user);

            //$this->mailer->sendAccountCreationEmail($name, $email, $password);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Compte créé avec succès.',
                'context' => 'global',
            ];
            header('Location: /dashboard/users');
            exit;
        }

        header('Location: /dashboard/users');
    }

    public function updateUser(string $user_id)
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $type = $_POST['role'];
            $name = $_POST['name'];

            if (!$user_id) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'id utilisateur manquant.',
                    'context' => 'global',
                ];
                header('Location: /dashboard/users');
                exit;
            }

            if (!$email || !$type || !$name) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email, type et nom sont obligatoires.',
                    'context' => 'global',
                ];
                header('Location: /dashboard/users');
                exit;
            }

            if (!in_array($type, ['student', 'teacher', 'admin'])) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Role incorrect.',
                    'context' => 'global',
                ];
                header('Location: /dashboard/users');
                exit;
            }

            // Récupération de l'utilisateur par slug (UUID)
            $user = $this->userModel->getUserById($user_id);
            if (!$user) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé.',
                    'context' => 'global',
                ];
                header('Location: /dashboard/users');
                exit;
            }

            // Vérifie si l'utilisateur modifie son propre compte
            if ($user->getId() == $_SESSION['user']['id']) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Vous ne pouvez pas modifier votre propre compte.',
                    'context' => 'global',
                ];
                header('Location: /dashboard/users');
                exit;
            }

            // Mise à jour des informations utilisateur
            $user->setEmail($email);
            $user->setRole($type);
            $user->setName($name);

            $this->userModel->updateUserInformation($user);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Compte modifié avec succès.',
                'context' => 'global',
            ];
            header('Location: /dashboard/users');
            exit;
        }

        // Récupération de l'utilisateur avant l'affichage du formulaire
        $user = $this->userModel->getUserById($user_id);
        if (!$user) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.',
                'context' => 'global',
            ];
            header('Location: /dashboard/users');
            exit;
        }

        echo $this->twig->render('userController/updateUser.html.twig', ['user' => $user]);
    }

    public function deleteUser(string $user_id)
    {
        // Vérification si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        // Vérification si l'utilisateur a les droits d'admin
        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        // Vérification que la méthode HTTP est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Méthode non autorisée.',
                'context' => 'global',
            ];
            header('Location: /dashboard/users');
            exit;
        }

        // Récupérer l'utilisateur en utilisant son slug (UUID)
        $user = $this->userModel->getUserById($user_id);
        if (!$user) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.',
                'context' => 'global',
            ];
            header('Location: /dashboard/users');
            exit;
        }

        // Vérification que l'admin ne tente pas de supprimer son propre compte
        if ($user->getId() == $_SESSION['user']['id']) {
            // Suppression de la session si l'admin supprime son propre compte
            session_destroy();
        }

        // Suppression de l'utilisateur
        $this->userModel->deleteUser($user);

        $_SESSION['alert'] = [
            'status' => 'success',
            'message' => 'Compte supprimé avec succès.',
            'context' => 'global',
        ];

        //$this->mailer->sendAccountDeletionEmail($user->getName(), $user->getEmail());

        // Redirection vers la liste des utilisateurs
        header('Location: /dashboard/users');
        exit;
    }

    public function resetPassword(string $user_id)
    {

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!is_array($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
            echo $this->twig->render('defaultController/403.html.twig');
            exit;
        }

        if (!$_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Méthode non autorisée.',
            ];
            header('Location: /dashboard/users');
            exit;
        }


        if (!$user_id) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.',
                'context' => 'global',
            ];
            header('Location: /dashboard/users');
            exit;
        }

        $user = $this->userModel->getUserById($user_id);
        if (!$user) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.',
            ];
            header('Location: /dashboard/users');
            exit;
        }
        $password = bin2hex(random_bytes(12));
        $this->userModel->resetPassword($user, password_hash($password, PASSWORD_DEFAULT));
        $user = $this->userModel->getUserById($user_id);


        //$this->mailer->sendAccountResetPasswordByAdminEmail($user->getName(), $user->getEmail(), $password);
        $_SESSION['alert'] = [
            'status' => 'success',
            'message' => 'Un nouveau mot de passe a été envoyé à ' . $user->getEmail(),
        ];

        header('Location: /dashboard/users');
        exit;
    }
}
