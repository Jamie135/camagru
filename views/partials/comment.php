<li class="mb-2">
    <div class="d-flex gap-2 align-items-baseline">
        <strong class="small"><?= $this->e($comment['author']) ?></strong>
        <time class="text-body-secondary" style="font-size: .75rem"
              datetime="<?= $this->e($comment['created_at']) ?>"><?= $this->e($this->date($comment['created_at'])) ?></time>
    </div>

    <?php // Escaped first, then the line breaks put back: the other way round
          // would turn anything the escaping neutralised back into markup. ?>
    <p class="small mb-0"><?= nl2br($this->e($comment['body'])) ?></p>
</li>
