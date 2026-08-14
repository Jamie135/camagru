<?php

/**
 * Renders a template inside the layout.
 *
 * The template runs first and the layout second, so a page can set $this->title
 * before <head> is written.
 */

namespace app\core;

class View
{
    public string $title = 'Camagru';

    public function render(string $view, array $params = []): string
    {
        $content = $this->capture(ROOT_DIR . '/views/' . $view . '.php', $params);

        return $this->capture(ROOT_DIR . '/views/layouts/main.php', ['content' => $content]);
    }

    public function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function capture(string $__file, array $__params): string
    {
        extract($__params, EXTR_SKIP);

        ob_start();
        include $__file;

        return ob_get_clean();
    }
}
