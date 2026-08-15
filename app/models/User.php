<?php

/**
 * Reads and writes rows in the users table.
 *
 * Rows come back as plain associative arrays, or null when nothing matches.
 * Every password_hash() and password_verify() call in the project lives here.
 */

namespace app\models;

use app\core\Database;
use PDOException;

class User
{
    public static function findById(int $id): ?array
    {
        return self::fetchOne('SELECT * FROM users WHERE id = :id', ['id' => $id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return self::fetchOne(
            'SELECT * FROM users WHERE lower(email) = lower(:email)', ['email' => $email]
        );
    }

    public static function findByUsername(string $username): ?array
    {
        return self::fetchOne(
            'SELECT * FROM users WHERE lower(username) = lower(:username)', ['username' => $username]
        );
    }

    public static function findByConfirmationToken(string $token): ?array
    {
        return self::fetchOne(
            'SELECT * FROM users WHERE confirmation_token = :token',
            ['token' => $token]
        );
    }

    // Inserts username, email, and password at signup. Returns the new user's ID.
    public static function create(string $username, string $email, string $password): int
    {
        $sql = 'INSERT INTO users (username, email, password_hash, confirmation_token)
                VALUES (:username, :email, :hash, :token)
                RETURNING id';

        $statement = Database::connection()->prepare($sql);
        // Catch duplicate key errors and throw a more specific exception.
        try {
            $statement->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' =>password_hash($password, PASSWORD_DEFAULT),
                'confirmation_token' => self::newToken(),
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $field = self::duplicateField($e->getMessage());
                if ($field) {
                    throw new DuplicateUserException($field);
                }
            }
            throw $e;
        }
        return $statement->fetch()['id'];
    }

    // Sets confirmation timestamp and clear the token if the token matches a row
    public static function confirm(string $token): bool
    {
        $sql = 'UPDATE users 
                SET confirmed_at = now(), confirmation_token = NULL
                WHERE confirmation_token = :token';
            
        $statement = Database::connection()->prepare($sql);
        $statement->execute(['token' => $token]);

        return $statement->rowCount() === 1;
    }

    public static function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }

    public static function isConfirmed(array $user): bool
    {
        return $user['confirmed_at'] !== null;
    }

    public static function newToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private static function duplicateField(string $message): ?string
    {
        return match (true) {
            str_contains($message, 'user_email_lower_idx') => 'email',
            str_contains($message, 'user_username_lower_idx') => 'username',
            default => null,
        };
    }

    private static function fetchOne(string $sql, array $params): ?array
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetch() ?: null;
    }
}
