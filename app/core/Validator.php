<?php

/**
 * Checks submitted form data and collects one message per bad field.
 */

namespace app\core;

class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    // Returns the trimmed value of a field.
    public function value(string $field): string
    {
        $value = $this->data[$field] ?? '';

        return is_string($value) ? trim($value) : '';
    }

    public function required(string $field, ?string $label = null): static
    {
        if ($this->skip($field)) {
            return $this;
        }

        if ($this->value($field) === '') {
            $this->fail($field, sprintf('%s is required.', $this->label($field, $label)));
        }

        return $this;
    }

    
    public function email(string $field, ?string $label = null): static
    {
        if ($this->skip($field) || $this->value($field) === '') {
            return $this;
        }

        if (filter_var($this->value($field), FILTER_VALIDATE_EMAIL) === false) {
            $this->fail($field, sprintf('%s is not a valid email address.', $this->label($field, $label)));
        }

        return $this;
    }

    public function length(string $field, int $min, int $max, ?string $label = null): static
    {
        if ($this->skip($field) || $this->value($field) === '') {
            return $this;
        }

        $length = mb_strlen($this->value($field));

        if ($length < $min || $length > $max) {
            $this->fail($field, sprintf(
                '%s must be between %d and %d characters.',
                $this->label($field, $label),
                $min,
                $max
            ));
        }

        return $this;
    }

    public function username(string $field, ?string $label = null): static
    {
        if ($this->skip($field) || $this->value($field) === '') {
            return $this;
        }

        if (preg_match('/^[a-zA-Z0-9_-]+$/', $this->value($field)) !== 1) {
            $this->fail($field, sprintf(
                '%s may only contain letters, digits, underscores and hyphens.',
                $this->label($field, $label)
            ));
        }

        return $this;
    }

    public function password(string $field, ?string $label = null): static
    {
        if ($this->skip($field) || $this->value($field) === '') {
            return $this;
        }

        $value = $this->value($field);

        if (mb_strlen($value) < 8 || preg_match('/[a-zA-Z]/', $value) !== 1 || preg_match('/\d/', $value) !== 1) {
            $this->fail($field, sprintf(
                '%s must be at least 8 characters and contain a letter and a digit.',
                $this->label($field, $label)
            ));
        }

        return $this;
    }

    public function matches(string $field, string $other, ?string $label = null): static
    {
        if ($this->skip($field)) {
            return $this;
        }

        if ($this->value($field) !== $this->value($other)) {
            $this->fail($field, sprintf('%s does not match.', $this->label($field, $label)));
        }

        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    // First failure per field wins, so one input never shows two messages.
    private function fail(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }

    private function skip(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    // 'confirm_password' -> 'Confirm password'
    private function label(string $field, ?string $label): string
    {
        return $label ?? ucfirst(str_replace('_', ' ', $field));
    }
}
