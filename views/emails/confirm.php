<h1 style="font-size: 20px; margin: 0 0 16px;">Welcome, <?= $this->e($username) ?>.</h1>

<p>One click and your Camagru account is ready to use:</p>

<p style="margin: 24px 0;">
    <a href="<?= $this->e($url) ?>" style="background: #0d6efd; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">Confirm my account</a>
</p>

<p style="font-size: 13px; color: #555;">
    If the button does not work, paste this into your browser:<br>
    <?= $this->e($url) ?>
</p>

<p style="font-size: 13px; color: #555;">
    Did not sign up? Ignore this message. The account cannot be used until the
    link above is opened, and it will never be sent to you again.
</p>
