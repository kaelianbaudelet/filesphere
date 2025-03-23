<?php
// src/Controller/AuthController.php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;

use App\Service\DependencyContainer;
use App\Service\Mailer;

use Twig\Environment;

use App\Model\UserModel;

class AuthController
{
    /**
     * @var Twig Instance de la classe Twig
     */
    private \Twig\Environment $twig;

    /**
     * @var UserModel Instance de la classe UserModel
     */
    private UserModel $userModel;

    /**
     * @var Mailer Instance de la classe Mailer
     */
    private Mailer $mailer;

    public function __construct(
        Environment $twig,
        DependencyContainer $dependencyContainer
    ) {
        $this->twig = $twig;
        $this->userModel = $dependencyContainer->get('UserModel');
        $this->mailer = new Mailer($twig);
    }

    /**
     * Affiche la page d'inscription temporaire.
     *
     * @return void
     */

    public function tempregister()
    {

        // Vérifie si la création de compte temporaire n'est pas activée
        if ($_ENV['TEMP_REGISTER'] != "true") {
            // Affiche une page d'erreur 404
            echo $this->twig->render('defaultController/404.html.twig', []);
        }

        // Vérifie si l'utilisateur est connecté
        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }

        // Vérifie si la méthode HTTP est POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];
            $role = $_POST['role'];
            $name = $_POST['name'];

            // Vérifie si les champs obligatoires sont remplis
            if (!$email || !$password || !$role || !$name) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email, mot de passe, type et nom sont obligatoires.',
                ];
                header('Location: /register');
                exit;
            }

            // Vérifie si le rôle est correct
            if (!in_array($role, ['student', 'teacher', 'admin'])) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Role incorrect.',
                ];
                header('Location: /register');
                exit;
            }

            // Vérifie si l'email est déjà utilisé
            $user = $this->userModel->getUserByEmail($email);
            if ($user) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email déjà utilisé.',
                ];
                header('Location: /register');
                exit;
            }

            // Crée un nouvel objet utilisateur
            $user = new User(
                null,
                $role,
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                true,
                null,
                null,
                null,
                null,
            );

            // Crée l'utilisateur
            $this->userModel->createUser($user);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Compte créé avec succès.',
            ];

            // Récupère l'utilisateur créé précédemment
            $user = $this->userModel->getUserByEmail($email);

            // Créer une session utilisateur
            $_SESSION['user'] = [
                'id' => $user->getId(),
                'role' => $user->getRole(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'is_active' => $user->getIsActive(),
            ];

            // Redirige vers le tableau de bord
            header('Location: /dashboard');
            exit;
        }
        // Affiche le formulaire d'inscription
        echo $this->twig->render('authController/register.html.twig', []);
    }


    /**
     * Affiche la page de connexion.
     *
     * @return void
     */

    public function login()
    {

        // Vérifie si l'utilisateur est connecté
        if (isset($_SESSION['user'])) {
            // Redirige vers le tableau de bord
            header('Location: /dashboard');
            exit;
        }

        // Vérifie si la méthode HTTP est POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];

            // Vérifie si les champs obligatoires sont remplis
            if (!$email || !$password) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email et mot de passe sont obligatoires.',
                ];
                header('Location: /login');
                exit;
            }

            // Récupère l'utilisateur par email
            $user = $this->userModel->getUserByEmail($email);

            // Vérifie si l'utilisateur existe et si le mot de passe est correct
            if ($user && password_verify($password, $user->getPassword())) {

                $_SESSION['user'] = [
                    'id' => $user->getId(),
                    'role' => $user->getRole(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'is_active' => $user->getIsActive(),
                ];

                // Redirige vers le tableau de bord
                header('Location: /dashboard');
                exit;
            } else {
                // Affiche un message d'erreur si l'email ou le mot de passe est incorrect
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email ou mot de passe incorrect.',
                ];
                // Redirige vers la page de connexion
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

                try {
                    $this->mailer->sendAccountResetPasswordEmail($user->getId(), $user->getName(), $user->getEmail(), $token);
                } catch (\Exception $e) {
                    $_SESSION['alert'] = ['status' => 'error', 'message' => "Une erreur est survenue lors de l'envoi de l'email."];
                    header('Location: /resetpassword');
                    exit;
                }
            }

            $_SESSION['alert'] = ['status' => 'success', 'message' => "Si cet email est enregistré, un lien de réinitialisation a été envoyé.", 'context' => 'global'];
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
