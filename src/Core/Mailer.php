<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envoi des e-mails transactionnels.
 * - MAIL_DRIVER=smtp : envoi réel via SMTP (Brevo, Mailtrap, Gmail…) avec PHPMailer
 * - MAIL_DRIVER=log  : l'e-mail est écrit dans storage/mails/ (développement)
 * Un échec d'envoi est journalisé mais ne bloque jamais le parcours utilisateur.
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $template, array $data = []): bool
    {
        $html = View::render('emails/' . $template, array_merge($data, ['subject' => $subject]), 'emails/layout');

        if (Env::get('MAIL_DRIVER', 'log') !== 'smtp') {
            return self::log($to, $subject, $html);
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = (string) Env::get('MAIL_HOST');
            $mail->Port       = (int) Env::get('MAIL_PORT', '587');
            // Authentification seulement si un identifiant est fourni (Mailpit en local n'en demande pas)
            $mail->SMTPAuth   = Env::get('MAIL_USERNAME') !== null;
            $mail->Username   = (string) Env::get('MAIL_USERNAME');
            $mail->Password   = (string) Env::get('MAIL_PASSWORD');
            $encryption = Env::get('MAIL_ENCRYPTION', 'tls');
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom((string) Env::get('MAIL_FROM', 'no-reply@viteetgourmand.fr'), 'Vite & Gourmand');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = trim(html_entity_decode(strip_tags($html)));
            $mail->send();
            return true;
        } catch (MailException $e) {
            log_error('Échec envoi e-mail à ' . $to . ' : ' . $mail->ErrorInfo);
            return false;
        }
    }

    private static function log(string $to, string $subject, string $html): bool
    {
        $dir = BASE_PATH . '/storage/mails';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $file = sprintf('%s/%s_%s.html', $dir, date('Ymd_His'), bin2hex(random_bytes(3)));
        $header = sprintf("<!-- To: %s | Subject: %s -->\n", $to, $subject);
        return file_put_contents($file, $header . $html) !== false;
    }
}
