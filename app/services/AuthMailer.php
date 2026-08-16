<?php

/**
 * Builds and sends the account emails: the confirmation link, the password
 * reset link, and the notice that goes out when someone tries to sign up with
 * an address that is already registered.
 */

namespace app\services;

class AuthMailer extends TemplateMailer
{
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
}
