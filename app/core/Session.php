<?php

/**
 * Owns the PHP session: starts it with hardened cookie flags, and provides
 * the read and write helpers the rest of the application uses.
 */

namespace app\core;

class Session
{
    private const FLASH_KEY = '__flash';

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_NONE || headers_sent()) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path'=> '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS'])
        ]);

        session_start();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    // Regenerate session ID by deleting the old one and creating a new one. 
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    // Destroy the session completely, including the cookie on the client side.
    public function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'],
            ]);
        }

        session_destroy();
    }

    // Flash messages are stored in the session for a request.
    // Useful for one-time notifications during that request.
    public function flash(string $type, string $message): void
    {
        $_SESSION[self::FLASH_KEY][$type] = $message;
    }

    // Retrieves and removes flash messages from the session.
    public function takeFlashes(): array
    {
        $flashes = $_SESSION[self::FLASH_KEY] ?? [];
        unset($_SESSION[self::FLASH_KEY]);

        return $flashes;
    }
}
