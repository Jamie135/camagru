<h1 style="font-size: 20px; margin: 0 0 16px;">Hello, <?= $this->e($username) ?>.</h1>

<p><strong><?= $this->e($commenter) ?></strong> has commented on one of your photos:</p>

<blockquote style="margin: 24px 0; padding: 12px 16px; border-left: 3px solid #dee2e6; color: #333;">
    <?= nl2br($this->e($body)) ?>
</blockquote>

<p style="margin: 24px 0;">
    <a href="<?= $this->e($url) ?>" style="background: #0d6efd; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">See the photo</a>
</p>

<p style="font-size: 13px; color: #555;">
    If the button does not work, paste this into your browser:<br>
    <?= $this->e($url) ?>
</p>

<p style="font-size: 13px; color: #555;">
    Would rather not hear about comments? Turn them off under
    <a href="<?= $this->e($settingsUrl) ?>"><?= $this->e($settingsUrl) ?></a>.
</p>
