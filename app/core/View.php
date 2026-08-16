<?php

/**
 * Renders a template inside the layout.
 */

namespace app\core;

use DateTimeImmutable;

class View
{
    public string $title = 'Camagru';

    public array $flashes = [];

    public string $csrfToken = '';

    public ?array $user = null;

    public string $path = '/';

    public function csrfField(): string
    {
        return '<input type="hidden" name="' . Csrf::FIELD . '" value="' . $this->e($this->csrfToken) . '">';
    }

    // The message under one input, when that input has one.
    public function fieldError(array $errors, string $field): string
    {
        if (!isset($errors[$field])) {
            return '';
        }

        return '<div class="invalid-feedback d-block">' . $this->e($errors[$field]) . '</div>';
    }

    public function render(string $view, array $params = []): string
    {
        return $this->capture(ROOT_DIR . '/views/layouts/main.php', [
            'content' => $this->renderPartial($view, $params),
        ]);
    }

    // A template on its own, with no layout wrapped around it: email bodies,
    // which have no navbar and no flash messages to show.
    public function renderPartial(string $view, array $params = []): string
    {
        return $this->capture(ROOT_DIR . '/views/' . $view . '.php', $params);
    }

    public function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // A timestamp out of the database.
    public function date(string $timestamp): string
    {
        return (new DateTimeImmutable($timestamp))->format('j M Y, H:i');
    }

    protected function capture(string $__file, array $__params): string
    {
        extract($__params, EXTR_SKIP);

        ob_start();
        include $__file;

        return ob_get_clean();
    }
}
