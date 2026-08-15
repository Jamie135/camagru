<?php
$section ??= '';
$errors ??= [];
$old ??= [];

$user = $this->user;

// Errors belong to whichever form was submitted; the other three show none.
$in = fn (string $name): array => $section === $name ? $errors : [];
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <h1 class="h3 my-4">Your account</h1>

        <?php if ($user['pending_email'] !== null): ?>
            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                <div>
                    Waiting for <strong><?= $this->e($user['pending_email']) ?></strong> to be confirmed.
                    Until then this account keeps using <?= $this->e($user['email']) ?>.
                </div>
                <form method="post" action="/profile/cancel-email-change" class="ms-3">
                    <?= $this->csrfField() ?>
                    <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap">Cancel</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Username ------------------------------------------------------->
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 card-title">Username</h2>

                <form method="post" action="/profile/username" novalidate>
                    <?= $this->csrfField() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?= $this->e($old['username'] ?? $user['username']) ?>" required>
                        <?= $this->fieldError($in('username'), 'username') ?>
                    </div>

                    <div class="mb-3">
                        <label for="username_current" class="form-label">Current password</label>
                        <input type="password" class="form-control" id="username_current"
                               name="current_password" autocomplete="current-password" required>
                        <?= $this->fieldError($in('username'), 'current_password') ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Change username</button>
                </form>
            </div>
        </div>

        <!-- Email ---------------------------------------------------------->
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 card-title">Email address</h2>
                <p class="text-body-secondary small">
                    We will send a link to the new address. The change only takes
                    effect once you open it.
                </p>

                <form method="post" action="/profile/email" novalidate>
                    <?= $this->csrfField() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= $this->e($old['email'] ?? $user['email']) ?>" required>
                        <?= $this->fieldError($in('email'), 'email') ?>
                    </div>

                    <div class="mb-3">
                        <label for="email_current" class="form-label">Current password</label>
                        <input type="password" class="form-control" id="email_current"
                               name="current_password" autocomplete="current-password" required>
                        <?= $this->fieldError($in('email'), 'current_password') ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Change email</button>
                </form>
            </div>
        </div>

        <!-- Password ------------------------------------------------------->
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 card-title">Password</h2>

                <form method="post" action="/profile/password" novalidate>
                    <?= $this->csrfField() ?>

                    <div class="mb-3">
                        <label for="password_current" class="form-label">Current password</label>
                        <input type="password" class="form-control" id="password_current"
                               name="current_password" autocomplete="current-password" required>
                        <?= $this->fieldError($in('password'), 'current_password') ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New password</label>
                        <input type="password" class="form-control" id="password" name="password"
                               autocomplete="new-password" required>
                        <?= $this->fieldError($in('password'), 'password') ?>
                        <div class="form-text">
                            At least 8 characters, with an uppercase letter, a lowercase letter and a digit.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm new password</label>
                        <input type="password" class="form-control" id="confirm_password"
                               name="confirm_password" autocomplete="new-password" required>
                        <?= $this->fieldError($in('password'), 'confirm_password') ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Change password</button>
                </form>
            </div>
        </div>

        <!-- Notifications -------------------------------------------------->
        <div class="card mb-5">
            <div class="card-body">
                <h2 class="h5 card-title">Notifications</h2>

                <form method="post" action="/profile/notifications">
                    <?= $this->csrfField() ?>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="notify_on_comment" name="notify_on_comment"
                               <?= $user['notify_on_comment'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="notify_on_comment">
                            Email me when someone comments on one of my photos
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
