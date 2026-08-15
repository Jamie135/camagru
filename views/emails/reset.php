<h1 style="font-size: 20px; margin: 0 0 16px;">Reset your password</h1>

<p>Hello <?= $this->e($username) ?>, someone asked to set a new password on your Camagru account.</p>

<p style="margin: 24px 0;">
    <a href="<?= $this->e($url) ?>" style="background: #0d6efd; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">Choose a new password</a>
</p>

<p style="font-size: 13px; color: #555;">
    If the button does not work, paste this into your browser:<br>
    <?= $this->e($url) ?>
</p>

<p style="font-size: 13px; color: #555;">
    This link stops working in one hour, and once it has been used it cannot be
    used again. If you did not ask for it, ignore this message — your password
    stays as it is.
</p>
