<h1 style="font-size: 20px; margin: 0 0 16px;">Confirm your new password</h1>

<p>Hello <?= $this->e($username) ?>, a new password was chosen for your Camagru account.</p>

<p style="margin: 24px 0;">
    <a href="<?= $this->e($url) ?>" style="background: #0d6efd; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">Confirm my new password</a>
</p>

<p style="font-size: 13px; color: #555;">
    If the button does not work, paste this into your browser:<br>
    <?= $this->e($url) ?>
</p>

<p style="font-size: 13px; color: #555;">
    Until this link is opened the new password does not work and your old one
    still does, so nothing is lost if you ignore this message. The link stops
    working in one hour.
</p>
