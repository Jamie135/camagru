<?php
$errors ??= [];
$old ??= [];
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 my-4">Forgotten your password?</h1>

        <p class="text-body-secondary">
            Type the address you signed up with and we will email you a link to
            set a new password. The link works for one hour.
        </p>

        <form method="post" action="/forgot-password" novalidate>
            <?= $this->csrfField() ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= $this->e($old['email'] ?? '') ?>" autocomplete="email" required>
                <?= $this->fieldError($errors, 'email') ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">Email me a link</button>
        </form>

        <p class="mt-3 mb-5 text-body-secondary">
            Remembered it? <a href="/login">Sign in</a>.
        </p>
    </div>
</div>
