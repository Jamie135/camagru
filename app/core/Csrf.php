<?php

/**
 * Proves that a POST came from one of our own pages.
 *
 * A random token is kept in the session and echoed into every form. An
 * attacker's page can send requests to us, but the same-origin policy stops it
 * reading our responses, so it can never learn the token to include one.
 */

namespace app\core;

class Csrf
{
    public const FIELD = 'csrf_token';

    private const KEY = '__csrf_token';

    public function __construct(private Session $session)
    {
    }

    // One token per session: reused by every form, so multiple tabs keep working.
    public function token(): string
    {
        $token = $this->session->get(self::KEY);

        if (!is_string($token) || $token === '') {
            $token = $this->rotate();
        }

        return $token;
    }

    // Call after a successful login, alongside Session::regenerate().
    public function rotate(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set(self::KEY, $token);

        return $token;
    }

    public function verify(mixed $submitted): bool
    {
        $token = $this->session->get(self::KEY);

        // $submitted comes straight from $_POST, so it can be an array or null.
        if (!is_string($token) || !is_string($submitted)) {
            return false;
        }

        // Compares in constant time: a plain === returns as soon as two bytes
        // differ, which leaks the token one byte at a time to anyone timing us.
        return hash_equals($token, $submitted);
    }
}
