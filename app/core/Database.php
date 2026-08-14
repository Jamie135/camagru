<?php

/**
 * This class manages the database connection using PDO.
 * It ensures that only one connection is created and reused throughout the application.
 */

namespace app\core;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    // Returns the single PDO connection, creating it if it doesn't exist yet.
    public static function connection(): PDO
    {
        return self::$connection ??= self::connect();
    }

    // Creates a new PDO connection using environment variables.
    private static function connect(): PDO
    {
        $host = self::env('DB_HOST') ?? 'db';
        $port = self::env('DB_PORT') ?? '5432';
        $name = self::env('DB_NAME');
        $user = self::env('DB_USER');
        $password = self::env('DB_PASSWORD');

        if ($name === null || $user === null || $password === null) {
            throw new RuntimeException('DB_NAME, DB_USER and DB_PASSWORD must be set.');
        }

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);

        return new PDO($dsn, $user, $password, [
            // Set PDO options for error handling, prepared statements, and fetch mode.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
    }

    // Retrieves an environment variable, returning null if it is not set or empty.
    private static function env(string $key): ?string
    {
        $value = getenv($key);

        return $value === false || $value === '' ? null : $value;
    }
}
