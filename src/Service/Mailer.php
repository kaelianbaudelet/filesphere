<?php
// src/Service/Mailer.php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Twig\Environment;

/**
 * Service de gestion des mails
 */
class Mailer
{

    /**
     * @var PHPMailer Instance de la classe PHPMailer
     */
    private $mail;

    /**
     * @var Environment Instance de la classe Twig
     */
    private $twig;

    public function __construct(Environment $twig)

    {

        $this->mail = new PHPMailer(false);
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->mail->isSMTP();
        $this->mail->Host       = (string)($_ENV['SMTP_HOST'] ?? '');
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = (string)($_ENV['SMTP_USER'] ?? '');
        $this->mail->Password   = (string)($_ENV['SMTP_PASSWORD'] ?? '');
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port       = isset($_ENV['SMTP_PORT']) ? (int)$_ENV['SMTP_PORT'] : 465;
        $this->mail->CharSet = 'UTF-8';
        $this->twig = $twig;
        $this->mail->setFrom((string)($_ENV['SMTP_SEND_EMAIL'] ?? ''), (string)($_ENV['SMTP_SEND_NAME'] ?? ''));
    }

    /**
     * Envoie un mail.
     *
     * @param string $to Adresse mail du destinataire
     * @param string $toName Nom du destinataire
     * @param string $subject Sujet du mail
     * @param string $body Contenu HTML du mail
     * @param string $altBody Contenu texte du mail
     * @return void
     */
    private function send($to, $toName, $subject, $body, $altBody)
    {

        $this->mail->addAddress($to, $toName);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body    = $body;
        $this->mail->AltBody = $altBody;

        $this->mail->send();
    }

    /**
     * Envoie un mail de création de compte.
     *
     * @param string $name Nom de l'utilisateur
     * @param string $email Adresse mail de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @return void
     */
    public function sendAccountCreationEmail(string $name, string $email, string $password)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Votre compte à bien été créé';

            $body = $this->twig->render('email/accountCreation.html.twig', [
                'name' => $name,
                'email' => $email,
                'password' => $password
            ]);

            $altBody = 'Votre compte à bien été créé. Vos identifiants sont : ' . $email . ' et ' . $password;

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    /**
     * Envoie un mail de suppression de compte.
     *
     * @param string $name Nom de l'utilisateur
     * @param string $email Adresse mail de l'utilisateur
     * @return void
     */
    public function sendAccountDeletionEmail(string $name, string $email)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Votre compte été supprimé par un administrateur';

            $body = $this->twig->render('email/accountDeletion.html.twig', [
                'name' => $name,
                'email' => $email,
            ]);

            $altBody = 'Votre compte a été supprimé par un administrateur';

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    /**
     * Envoie un mail de réinitialisation de mot de passe par un administrateur.
     *
     * @param string $name Nom de l'utilisateur
     * @param string $email Adresse mail de l'utilisateur
     * @param string $password Nouveau mot de passe de l'utilisateur
     * @return void
     */
    public function sendAccountResetPasswordByAdminEmail(string $name, string $email, string $password)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Votre mot de passe a été réinitialisé par un administrateur';

            $body = $this->twig->render('email/accountResetPasswordByAdmin.html.twig', [
                'name' => $name,
                'email' => $email,
                'password' => $password
            ]);

            $altBody = 'Votre mot de passe a été réinitialisé par un administrateur. Vos nouveaux identifiants sont : ' . $email . ' et ' . $password;

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    /**
     * Envoie un mail de réinitialisation de mot de passe.
     *
     * @param string $user_id Identifiant de l'utilisateur
     * @param string $name Nom de l'utilisateur
     * @param string $email Adresse mail de l'utilisateur
     * @param string $resetToken Token de réinitialisation de mot de passe
     * @return void
     */
    public function sendAccountResetPasswordEmail(string $user_id, string $name, string $email, string $resetToken)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Réinitialisation de votre mot de passe';

            $appUrl = $_ENV['APP_URL'] ?? '';
            $body = $this->twig->render('email/accountResetPassword.html.twig', [
                'user_id' => $user_id,
                'name' => $name,
                'email' => $email,
                'link' => "{$appUrl}/resetpassword?token={$resetToken}&user_id={$user_id}"
            ]);

            $altBody = "Pour réinitialiser votre mot de passe, cliquez sur le lien suivant : {$appUrl}/resetpassword?token={$resetToken}&user_id={$user_id}";

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    /**
     * Envoie un mail de réinitialisation de mot de passe.
     *
     * @param string $name Nom de l'utilisateur
     * @param string $email Adresse mail de l'utilisateur
     * @return void
     */
    public function sendClassJoinEmail(string $name, string $email, string $className)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Vous avez été ajouté à une classe';

            $body = $this->twig->render('email/classJoin.html.twig', [
                'name' => $name,
                'email' => $email,
                'className' => $className,
            ]);

            $altBody = 'Vous avez été ajouté à la classe ' . $className;

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    /**
     * Envoie un mail de réinitialisation de mot de passe.
     *
     * @param string $name Nom de l'utilisateur
     * @param string $email Adresse mail de l'utilisateur
     * @return void
     */
    public function sendAccountResetPasswordSuccessNotification(string $name, string $email)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Votre mot de passe a été modifié';

            $body = $this->twig->render('email/accountResetPasswordSuccessNotification.html.twig', [
                'name' => $name,
                'email' => $email,
            ]);

            $altBody = 'Votre mot de passe a été modifié avec succès';

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}
