<?php
$errors ??= [];
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="page-title">Choose a new password</h1>

        <form method="post" action="/reset-password/<?= $this->e($token) ?>" novalidate>
            <?= $this->csrfField() ?>

            <div class="mb-3">
                <label for="password" class="form-label">New password</label>
                <input type="password" class="form-control" id="password" name="password"
                       autocomplete="new-password" required>
                <?= $this->fieldError($errors, 'password') ?>
                <div class="form-text">
                    At least 8 characters, with an uppercase letter, a lowercase letter and a digit.
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm new password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                       autocomplete="new-password" required>
                <?= $this->fieldError($errors, 'confirm_password') ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">Change my password</button>
        </form>
    </div>
</div>
