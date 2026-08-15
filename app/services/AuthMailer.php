<?php

/**
 * Builds and sends the account emails: the confirmation link, the password
 * reset link, and the notice that goes out when someone tries to sign up with
 * an address that is already registered.
 */

namespace app\services;

use app\core\Mailer;
use app\core\View;
use Throwable;

class AuthMailer
{
    public function __construct(
        private Mailer $mailer,
        private View $view,
        private string $appUrl,
    ) {
    }

    public static function fromEnv(): self
    {
        $appUrl = getenv('APP_URL') ?: 'http://localhost:8080';

        return new self(Mailer::fromEnv(), new View(), rtrim($appUrl, '/'));
    }

    public function sendConfirmation(string $email, string $username, string $token): bool
    {
        return $this->deliver($email, 'Confirm your Camagru account', 'confirm', [
            'username' => $username,
            'url' => $this->url('/verify/' . $token),
        ]);
    }

    public function sendPasswordReset(string $email, string $username, string $token): bool
    {
        return $this->deliver($email, 'Reset your Camagru password', 'reset', [
            'username' => $username,
            'url' => $this->url('/reset-password/' . $token),
        ]);
    }

    // Sent to the address being moved to, never to the one already on file:
    // the point is to prove that the new mailbox exists and is reachable.
    public function sendEmailChange(string $email, string $username, string $token): bool
    {
        return $this->deliver($email, 'Confirm your new Camagru address', 'email-change', [
            'username' => $username,
            'url' => $this->url('/profile/confirm-email/' . $token),
        ]);
    }

    public function sendDuplicateSignupNotice(string $email): bool
    {
        return $this->deliver($email, 'You already have a Camagru account', 'duplicate-signup', [
            'loginUrl' => $this->url('/login'),
            'resetUrl' => $this->url('/forgot-password'),
        ]);
    }

    // This is a private helper method that does the actual work of sending an email.
    // It renders the email template and then uses the Mailer service to send it. If sending fails, it logs the error and returns false.
    private function deliver(string $to, string $subject, string $template, array $params): bool
    {
        try {
            $this->mailer->send($to, $subject, $this->render($template, $params));

            return true;
        } catch (Throwable $e) {
            error_log(sprintf(
                'Mail "%s" to <%s> failed: %s: %s',
                $subject,
                $to,
                $e::class,
                $e->getMessage()
            ));

            return false;
        }
    }

    private function render(string $template, array $params): string
    {
        return $this->view->renderPartial('emails/email_layout', [
            'content' => $this->view->renderPartial('emails/' . $template, $params),
        ]);
    }

    private function url(string $path): string
    {
        return $this->appUrl . $path;
    }
}
