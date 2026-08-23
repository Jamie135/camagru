<?php

/**
 * Account settings: username, email address, password, and whether a comment
 * on your photo should be emailed to you..
 */

namespace app\controllers;

use app\core\Controller;
use app\core\Validator;
use app\models\DuplicateUserException;
use app\models\User;
use app\services\AuthMailer;

class ProfileController extends Controller
{
    private const WRONG_PASSWORD = 'That is not your current password.';

    private const EMAIL_SENT = 'Check that inbox — we have sent it a link to confirm the change.';

    private const PASSWORD_LINK_SENT = 'Check your inbox — we have sent you a link to set a new password.';

    private ?AuthMailer $mailer = null;

    public function index(): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        return $this->form();
    }

    // -----------------------------------------------------------------------

    public function updateUsername(): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        $validator = new Validator($this->request->body());
        $validator
            ->required('username')->length('username', 3, 32)->username('username')
            ->required('current_password');

        $username = $validator->value('username');

        if ($validator->fails()) {
            return $this->form('username', $validator->errors(), ['username' => $username]);
        }

        if (($failure = $this->checkCurrentPassword($validator)) !== null) {
            return $this->form('username', $failure, ['username' => $username]);
        }

        $user = $this->auth->user();

        if (strcasecmp($username, $user['username']) === 0) {
            $this->session->flash('info', 'That is already your username.');

            return $this->redirect('/profile');
        }

        try {
            User::updateUsername((int) $user['id'], $username);
        } catch (DuplicateUserException) {
            return $this->form('username', ['username' => 'That username is taken.'], ['username' => $username]);
        }

        $this->session->flash('success', 'Your username has been changed.');

        return $this->redirect('/profile');
    }

    // -----------------------------------------------------------------------

    public function updateEmail(): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        $validator = new Validator($this->request->body());
        $validator
            ->required('email')->email('email')->length('email', 3, 255)
            ->required('current_password');

        $email = $validator->value('email');

        if ($validator->fails()) {
            return $this->form('email', $validator->errors(), ['email' => $email]);
        }

        if (($failure = $this->checkCurrentPassword($validator)) !== null) {
            return $this->form('email', $failure, ['email' => $email]);
        }

        $user = $this->auth->user();

        if (strcasecmp($email, $user['email']) === 0) {
            $this->session->flash('info', 'That is already your address.');

            return $this->redirect('/profile');
        }

        $owner = User::findByEmail($email);

        if ($owner === null) {
            $token = User::newToken();

            // Nothing on the account moves yet. Only the link coming back does
            // that, which is what proves the new mailbox is really reachable.
            User::startEmailChange((int) $user['id'], $email, $token);
            $this->mailer()->sendEmailChange($email, $user['username'], $token);
        } else {
            // Taken. Same answer on screen as a free address, with the notice
            // going to the mailbox instead — the rule that keeps signup from
            // telling strangers which addresses are registered applies here too.
            $this->mailer()->sendDuplicateSignupNotice($email);
        }

        $this->session->flash('info', self::EMAIL_SENT);

        return $this->redirect('/profile');
    }

    public function confirmEmail(string $token): string
    {
        try {
            $changed = User::confirmEmailChange($token);
        } catch (DuplicateUserException) {
            $this->session->flash('danger', 'That address was registered by someone else in the meantime.');

            return $this->redirect('/profile');
        }

        if ($changed) {
            $this->session->flash('success', 'Your email address has been changed.');
        } else {
            $this->session->flash('danger', 'That link has expired or has already been used.');
        }

        return $this->redirect($this->auth->check() ? '/profile' : '/login');
    }

    public function cancelEmailChange(): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        User::cancelEmailChange((int) $this->auth->id());
        $this->session->flash('info', 'The pending address change has been dropped.');

        return $this->redirect('/profile');
    }

    // -----------------------------------------------------------------------

    // No password is typed here: the account is sent the same one-time link as
    // a forgotten password, and /reset-password is the only place it changes.
    public function sendPasswordLink(): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        $user = $this->auth->user();
        $token = User::newToken();

        User::startPasswordReset((int) $user['id'], $token);
        $this->mailer()->sendPasswordReset($user['email'], $user['username'], $token);

        $this->session->flash('info', self::PASSWORD_LINK_SENT);

        return $this->redirect('/profile');
    }

    // -----------------------------------------------------------------------

    public function updateNotifications(): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        // An unticked checkbox is not submitted at all, so absence means off.
        $enabled = $this->request->post('notify_on_comment') !== null;

        User::setNotifyOnComment((int) $this->auth->id(), $enabled);

        $this->session->flash(
            'success',
            $enabled
                ? 'You will be emailed when someone comments on your photos.'
                : 'You will no longer be emailed about comments.'
        );

        return $this->redirect('/profile');
    }

    // -----------------------------------------------------------------------

    private function checkCurrentPassword(Validator $validator): ?array
    {
        if (User::verifyPassword($this->auth->user(), $validator->value('current_password'))) {
            return null;
        }

        return ['current_password' => self::WRONG_PASSWORD];
    }

    private function form(string $section = '', array $errors = [], array $old = []): string
    {
        $this->view->title = 'Your account';

        return $this->render('profile/index', [
            'section' => $section,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function mailer(): AuthMailer
    {
        return $this->mailer ??= AuthMailer::fromEnv();
    }
}
