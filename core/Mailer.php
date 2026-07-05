<?php

/**
 * Mailer
 *
 * PHPMailer wrapper for all outgoing emails.
 * Credentials loaded from .env via $_ENV.
 * Sender name passed from controller (derived from organisation_info.name).
 * 
 *  * Used for:
 *   - OTP authentication emails
 *   - Contact form notifications
 *   - Membership request notifications
 *
 * OTP expiry displayed in email comes from config/app.php → passed by controller. 
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Send an email.
     * 
     * @param string        $to         Recipient emails address
     * @param string        $subject    Email Subject
     * @param string        $body       HTML email boday
     * @param string|null   $fromName   Sender display name — from organisation_info.name
     * @return bool                     True on success, false on failure
     */
    public static function send(
        string $to,
        string $subject,
        string $body,
        ?string $fromName = null,
        ?string $replyTo  = null
    ): bool {

        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = (int) $_ENV['MAIL_PORT'];

            // Sender — name from organisation_info via controller
            $mail->setFrom(
                $_ENV['MAIL_FROM'],
                $fromName ?? $_ENV['MAIL_FROM'] // fallback to email if no name
            );

            // Recipient 
            $mail->addAddress($to);

            // Reply-to — set to sender's email for contact form
            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }

            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Render an email view template into an HTML string.
     * Templates live in app/Views/emails/
     *
     * @param string $view   View path relative to app/Views/ e.g. 'emails/otp'
     * @param array  $data   Variables to extract into the template
     * @return string        Rendered HTML string
     */
    public static function renderView(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        require __DIR__ . '/../app/Views/' . $view . '.php';
        return ob_get_clean();
    }
}
