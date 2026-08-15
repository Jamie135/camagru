<h1 style="font-size: 20px; margin: 0 0 16px;">Confirm your new address</h1>

<p>Hello <?= $this->e($username) ?>, you asked to use this address for your Camagru account.</p>

<p style="margin: 24px 0;">
    <a href="<?= $this->e($url) ?>" style="background: #0d6efd; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">Use this address</a>
</p>

<p style="font-size: 13px; color: #555;">
    If the button does not work, paste this into your browser:<br>
    <?= $this->e($url) ?>
</p>

<p style="font-size: 13px; color: #555;">
    Until this link is opened the account keeps using its old address, so
    nothing is lost if you ignore this message. The link stops working in one
    hour.
</p>
