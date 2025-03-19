<?php
// Path: src/Service/EmailService.php
namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Twig\Environment;

class Mailer
{
    private $mail;
    private $twig;
    public function __construct(Environment $twig)

    {

        $this->mail = new PHPMailer(false);

        //Configuring SMTP
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['SMTP_USER'];
        $this->mail->Password   = $_ENV['SMTP_PASSWORD'];
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port       = $_ENV['SMTP_PORT'];
        $this->mail->CharSet = 'UTF-8';
        $this->twig = $twig;
        $this->mail->setFrom('noreply@kaelian.dev', 'Hotel Neptune');
    }


    private function send($to, $toName, $subject, $body, $altBody)
    {

        $this->mail->addAddress($to, $toName);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body    = $body;
        $this->mail->AltBody = $altBody;

        $this->mail->send();
    }

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
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

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
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

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
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    // sendAccountResetPasswordEmail

    public function sendAccountResetPasswordEmail(string $user_id, string $name, string $email, string $resetToken)
    {
        try {
            $to = $email;
            $toName = $name;

            $subject = 'Réinitialisation de votre mot de passe';

            $body = $this->twig->render('email/accountResetPassword.html.twig', [
                'user_id' => $user_id,
                'name' => $name,
                'email' => $email,
                'link' => $_ENV['APP_URL'] . '/resetpassword?token=' . $resetToken . '&user_id=' . $user_id
            ]);

            $altBody = 'Pour réinitialiser votre mot de passe, cliquez sur le lien suivant : ' . $_ENV['APP_URL'] . '/resetpassword?token=' . $resetToken . '&user_id=' . $user_id;

            $this->send($to, $toName, $subject, $body, $altBody);
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

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
}
