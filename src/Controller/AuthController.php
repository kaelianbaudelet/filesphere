<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\DependencyContainer;
use Twig\Environment;
use App\Service\Mailer;

class AuthController
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

    public function tempregister()
    {

        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];
            $role = $_POST['role'];
            $name = $_POST['name'];

            if (!$email || !$password || !$role || !$name) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email, mot de passe, type et nom sont obligatoires.',
                ];
                header('Location: /register');
                exit;
            }

            if (!in_array($role, ['student', 'teacher', 'admin'])) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Role incorrect.',
                ];
                header('Location: /register');
                exit;
            }

            $user = $this->userModel->getUserByEmail($email);

            if ($user) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email déjà utilisé.',
                ];
                header('Location: /register');
                exit;
            }

            // model

            $user = new User(
                null, // id null par défaut (mariaDB)
                $role,
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                true, // is_active true par défaut (mariaDB)
                null, // reset_token null par défaut (mariaDB)
                null, // reset_token_expires_at null par défaut (mariaDB)
                null,
                null,
            );
            $this->userModel->createUser($user);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Compte créé avec succès.',
            ];

            $user = $this->userModel->getUserByEmail($email);

            $_SESSION['user'] = [
                'id' => $user->getId(),
                'role' => $user->getRole(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'is_active' => $user->getIsActive(),
            ];

            header('Location: /dashboard');
            exit;
        }
        echo $this->twig->render('authController/register.html.twig', []);
    }

    public function login()
    {
        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];

            if (!$email || !$password) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email et mot de passe sont obligatoires.',
                ];
                header('Location: /login');
                exit;
            }

            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user->getPassword())) {

                $_SESSION['user'] = [
                    'id' => $user->getId(),
                    'role' => $user->getRole(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'is_active' => $user->getIsActive(),
                ];

                header('Location: /dashboard');
                exit;
            } else {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email ou mot de passe incorrect.',
                ];
                header('Location: /login');
                exit;
            }
        }

        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }
        echo $this->twig->render('authController/login.html.twig', []);
    }

    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

            if (!$email) {
                $_SESSION['alert'] = ['status' => 'error', 'message' => "L'email est obligatoire."];
                header('Location: /resetpassword');
                exit;
            }

            $user = $this->userModel->getUserByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->setResetToken($token);
                $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));
                $this->userModel->updateUser($user);

                //->sendAccountResetPasswordEmail($user->getId(), $user->getName(), $user->getEmail(), $token);
            }

            $_SESSION['alert'] = ['status' => 'success', 'message' => "Si cet email est enregistré, un lien de réinitialisation a été envoyé."];
            header('Location: /resetpassword');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'], $_GET['user_id'])) {
            $token = $_GET['token'];
            $user = $this->userModel->getUserById($_GET['user_id']);

            if (!$user || $user->getResetToken() !== $token || $user->getResetTokenExpiresAt() < new \DateTime()) {
                $_SESSION['alert'] = ['status' => 'error', 'message' => "Le lien de réinitialisation est invalide ou expiré."];
                header('Location: /resetpassword');
                exit;
            }

            echo $this->twig->render('authController/resetpassword.html.twig', ['user_id' => $user->getId(), 'token' => $token]);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['user_id'], $_POST['newpassword'], $_POST['newpasswordconfirm'])) {
            $token = $_POST['token'];
            $user = $this->userModel->getUserById($_POST['user_id']);

            if (!$user || $user->getResetToken() !== $token || $user->getResetTokenExpiresAt() < new \DateTime()) {
                $_SESSION['alert'] = ['status' => 'error', 'message' => "Le lien de réinitialisation est invalide ou expiré."];
                header('Location: /resetpassword');
                exit;
            }

            $newPassword = $_POST['newpassword'];
            $newPasswordConfirm = $_POST['newpasswordconfirm'];

            if ($newPassword !== $newPasswordConfirm) {
                $_SESSION['alert'] = ['status' => 'error', 'message' => "Les mots de passe ne correspondent pas."];
                header("Location: /resetpassword?user_id={$user->getId()}&token={$token}");
                exit;
            }

            if (strlen($newPassword) < 8 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/\d/', $newPassword) || !preg_match('/[^a-zA-Z\d]/', $newPassword)) {
                $_SESSION['alert'] = ['status' => 'error', 'message' => "Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial."];
                header("Location: /resetpassword?user_id={$user->getId()}&token={$token}");
                exit;
            }

            $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            $this->userModel->updateUser($user);

            $_SESSION['alert'] = ['status' => 'success', 'message' => "Mot de passe réinitialisé avec succès, vous pouvez maintenant vous connecter."];
            header('Location: /login');
            exit;
        }

        echo $this->twig->render('authController/resetpassword.html.twig', []);
    }

    public function logout()
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}
