<?php

/**
 * This class handles user authentication and session management.
 */

namespace app\core;

use app\models\User;

class Auth
{
    private const KEY = 'user_id';

    private ?array $user = null;

    private bool $loaded = false;

    public function __construct(private Session $session)
    {
    }

    public function login(array $user): void
    {
        $this->session->regenerate();
        $this->session->set(self::KEY, (int) $user['id']);

        $this->user = $user;
        $this->loaded = true;
    }

    public function logout(): void
    {
        $this->session->invalidate();

        $this->user = null;
        $this->loaded = true;
    }

    public function id(): ?int
    {
        $id = $this->session->get(self::KEY);

        return is_int($id) ? $id : null;
    }

    public function user(): ?array
    {
        if ($this->loaded) {
            return $this->user;
        }

        $this->loaded = true;

        $id = $this->id();
        $this->user = $id === null ? null : User::findById($id);

        if ($this->user === null) {
            $this->session->remove(self::KEY);
        }

        return $this->user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }
}
