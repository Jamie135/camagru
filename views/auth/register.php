<?php
// Both are absent on the first GET, present once something failed.
$errors ??= [];
$old ??= [];
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 my-4">Create an account</h1>

        <form method="post" action="/register" novalidate>
            <?= $this->csrfField() ?>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= $this->e($old['username'] ?? '') ?>" autocomplete="username" required>
                <?= $this->fieldError($errors, 'username') ?>
                <div class="form-text">3 to 32 letters, digits, underscores or hyphens.</div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= $this->e($old['email'] ?? '') ?>" autocomplete="email" required>
                <?= $this->fieldError($errors, 'email') ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password"
                       autocomplete="new-password" required>
                <?= $this->fieldError($errors, 'password') ?>
                <div class="form-text">
                    At least 8 characters, with an uppercase letter, a lowercase letter and a digit.
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                       autocomplete="new-password" required>
                <?= $this->fieldError($errors, 'confirm_password') ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign up</button>
        </form>

        <p class="mt-3 mb-5 text-body-secondary">
            Already have an account? <a href="/login">Sign in</a>.
        </p>
    </div>
</div>
