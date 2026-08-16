<?php // One page of cards. Rendered into the grid, and again on its own each
      // time gallery.js asks for the next page. ?>
<?php foreach ($photos as $photo): ?>
    <div class="col">
        <?= $this->renderPartial('partials/photo-card', [
            'photo' => $photo,
            'thread' => $comments[$photo['id']] ?? [],
            'returnTo' => '/?page=' . $page,
        ]) ?>
    </div>
<?php endforeach; ?>
