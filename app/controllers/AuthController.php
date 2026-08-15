<?php

/**
 * Registration, email confirmation, signing in and out, 
 * and the forgotten password round trip.
 */

namespace app\controllers;

use app\core\Controller;
use app\core\Validator;
use app\models\DuplicateUserException;
use app\models\User;
use app\services\AuthMailer;

class AuthController extends Controller
{
    private const SIGNUP_SENT = 'Check your inbox — we have just sent you an email.';

    private const RESET_SENT = 'If that address has an account here, a link to reset it is on its way.';

    private const BAD_CREDENTIALS = 'Invalid credentials.';

    private const DEAD_RESET_LINK = 'That link has expired or has already been used. Ask for another one.';

    private ?AuthMailer $mailer = null;

    // -----------------------------------------------------------------------
    // Registration
    // -----------------------------------------------------------------------

    public function register(): string
    {
        if (($redirect = $this->requireGuest()) !== null) {
            return $redirect;
        }

        if (!$this->request->isPost()) {
            return $this->render('auth/register');
        }

        $validator = new Validator($this->request->body());
        $validator
            ->required('username')->length('username', 3, 32)->username('username')
            ->required('email')->email('email')->length('email', 3, 255)
            ->required('password')->password('password')->length('password', 8, 128)
            ->required('confirm_password')->matches('confirm_password', 'password');

        $username = $validator->value('username');
        $email = $validator->value('email');

        if ($validator->fails()) {
            return $this->registerForm($validator->errors(), $username, $email);
        }

        try {
            $token = User::newToken();

            User::create($username, $email, $validator->value('password'), $token);
            $this->mailer()->sendConfirmation($email, $username, $token);
        } catch (DuplicateUserException $e) {
            if ($e->field === 'username') {
                return $this->registerForm(['username' => 'That username is taken.'], $username, $email);
            }
            $this->mailToExistingAccount($email);
        }

        $this->session->flash('info', self::SIGNUP_SENT);

        return $this->redirect('/login');
    }

    public function verify(string $token): string
    {
        if (User::confirm($token)) {
            $this->session->flash('success', 'Your account is confirmed. You can sign in now.');
        } else {
            $this->session->flash('danger', 'That confirmation link is not valid — it may already have been used.');
        }

        return $this->redirect('/login');
    }

    // -----------------------------------------------------------------------
    // Sessions
    // -----------------------------------------------------------------------

    public function login(): string
    {
        if (($redirect = $this->requireGuest()) !== null) {
            return $redirect;
        }

        if (!$this->request->isPost()) {
            return $this->render('auth/login');
        }

        $validator = new Validator($this->request->body());
        $validator->required('login', 'Username or email')->required('password');

        $login = $validator->value('login');
        $password = $validator->value('password');

        if ($validator->fails()) {
            return $this->loginForm($validator->errors(), $login);
        }

        $user = str_contains($login, '@')
            ? User::findByEmail($login)
            : User::findByUsername($login);

        if (!User::verifyPassword($user, $password)) {
            return $this->loginForm(['login' => self::BAD_CREDENTIALS], $login);
        }

        if (!User::isConfirmed($user)) {
            return $this->loginForm(
                ['login' => 'Confirm your account first — check the email we sent you.'],
                $login
            );
        }

        if (User::needsRehash($user)) {
            User::updatePassword((int) $user['id'], $password);
        }

        $this->auth->login($user);
        $this->csrf->rotate();

        $this->session->flash('success', 'Welcome back, ' . $user['username'] . '.');

        return $this->redirect('/');
    }

    public function logout(): string
    {
        if ($this->auth->check()) {
            $this->auth->logout();
            $this->session->flash('success', 'You have been signed out.');
        }

        return $this->redirect('/');
    }

    // -----------------------------------------------------------------------
    // Forgotten password
    // -----------------------------------------------------------------------

    public function forgotPassword(): string
    {
        if (($redirect = $this->requireGuest()) !== null) {
            return $redirect;
        }

        if (!$this->request->isPost()) {
            return $this->render('auth/forgot-password');
        }

        $validator = new Validator($this->request->body());
        $validator->required('email')->email('email');

        $email = $validator->value('email');

        if ($validator->fails()) {
            return $this->render('auth/forgot-password', [
                'errors' => $validator->errors(),
                'old' => ['email' => $email],
            ]);
        }

        $user = User::findByEmail($email);

        if ($user !== null && User::isConfirmed($user)) {
            $token = User::newToken();

            User::startPasswordReset((int) $user['id'], $token);
            $this->mailer()->sendPasswordReset($email, $user['username'], $token);
        }

        $this->session->flash('info', self::RESET_SENT);

        return $this->redirect('/login');
    }

    // Password reset sent by email
    public function resetPassword(string $token): string
    {
        if (($redirect = $this->requireGuest()) !== null) {
            return $redirect;
        }

        if (User::findByResetToken($token) === null) {
            $this->session->flash('danger', self::DEAD_RESET_LINK);

            return $this->redirect('/forgot-password');
        }

        if (!$this->request->isPost()) {
            return $this->render('auth/reset-password', ['token' => $token]);
        }

        $validator = new Validator($this->request->body());
        $validator
            ->required('password')->password('password')->length('password', 8, 128)
            ->required('confirm_password')->matches('confirm_password', 'password');

        if ($validator->fails()) {
            return $this->render('auth/reset-password', [
                'token' => $token,
                'errors' => $validator->errors(),
            ]);
        }

        if (!User::resetPassword($token, $validator->value('password'))) {
            $this->session->flash('danger', self::DEAD_RESET_LINK);

            return $this->redirect('/forgot-password');
        }

        $this->session->invalidate();
        $this->session->flash('success', 'Your password has been changed. You can sign in with it now.');

        return $this->redirect('/login');
    }

    // -----------------------------------------------------------------------

    // Account that already exists and is unconfirmed gets a new confirmation email
    private function mailToExistingAccount(string $email): void
    {
        $existing = User::findByEmail($email);

        if ($existing !== null && !User::isConfirmed($existing) && $existing['confirmation_token'] !== null) {
            $this->mailer()->sendConfirmation($email, $existing['username'], $existing['confirmation_token']);

            return;
        }

        $this->mailer()->sendDuplicateSignupNotice($email);
    }

    private function registerForm(array $errors, string $username, string $email): string
    {
        return $this->render('auth/register', [
            'errors' => $errors,
            'old' => ['username' => $username, 'email' => $email],
        ]);
    }

    private function loginForm(array $errors, string $login): string
    {
        return $this->render('auth/login', [
            'errors' => $errors,
            'old' => ['login' => $login],
        ]);
    }

    // Built on demand: a GET that only draws a form never opens a socket.
    private function mailer(): AuthMailer
    {
        return $this->mailer ??= AuthMailer::fromEnv();
    }
}
