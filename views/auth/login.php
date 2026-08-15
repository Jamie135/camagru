<?php
$errors ??= [];
$old ??= [];
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h3 my-4">Sign in</h1>

        <form method="post" action="/login" novalidate>
            <?= $this->csrfField() ?>

            <div class="mb-3">
                <label for="login" class="form-label">Username or email</label>
                <input type="text" class="form-control" id="login" name="login"
                       value="<?= $this->e($old['login'] ?? '') ?>" autocomplete="username" required>
                <?= $this->fieldError($errors, 'login') ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password"
                       autocomplete="current-password" required>
                <?= $this->fieldError($errors, 'password') ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign in</button>
        </form>

        <p class="mt-3 mb-5 text-body-secondary">
            <a href="/forgot-password">Forgotten your password?</a>
            &middot;
            <a href="/register">Create an account</a>
        </p>
    </div>
</div>
