<?php

/**
 * Renders a template inside the layout.
 */

namespace app\core;

class View
{
    public string $title = 'Camagru';

    public array $flashes = [];

    public string $csrfToken = '';

    public function csrfField(): string
    {
        return '<input type="hidden" name="' . Csrf::FIELD . '" value="' . $this->e($this->csrfToken) . '">';
    }

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
