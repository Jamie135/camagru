<?php

/**
 * This class is a model for the users table. 
 * It contains methods for creating, reading, updating, and deleting users,
 * as well as for handling password hashing and verification, email confirmation, and password resets.
 */

namespace app\models;

use app\core\Database;
use PDOException;
use PDOStatement;

class User
{
    // The algorithm used for hashing passwords.
    // Argon2id is the most secure option available in PHP 7.3+.
    private const ALGORITHM = PASSWORD_ARGON2ID;

    // This is a hash of a password that will never be used. It is used to make
    // the time taken to verify a password for a non-existent user the same as
    // for a real user, so that an attacker cannot find out which emails are registered by timing the responses.
    private const ABSENT_USER_HASH =
        '$argon2id$v=19$m=65536,t=4,p=1$dkoublpXNExHZXcwcEZaeQ$vFS8n1bHDoW1QWA0trn2SG0qguJMFKjk6m1lUAKCgF8';

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
    public static function create(string $username, string $email, string $password, string $token): int
    {
        $statement = self::executeUnique(
            'INSERT INTO users (username, email, password_hash, confirmation_token)
                  VALUES (:username, :email, :hash, :token)
               RETURNING id',
            [
                'username' => $username,
                'email' => $email,
                'hash' => password_hash($password, self::ALGORITHM),
                'token' => $token,
            ]
        );

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

    public static function verifyPassword(?array $user, string $password): bool
    {
        if ($user === null) {
            password_verify($password, self::ABSENT_USER_HASH);

            return false;
        }

        return password_verify($password, $user['password_hash']);
    }

    public static function isConfirmed(array $user): bool
    {
        return $user['confirmed_at'] !== null;
    }

    // True for the seeded bcrypt rows, and for anything hashed before the
    // project moved to argon2id.
    public static function needsRehash(array $user): bool
    {
        return password_needs_rehash($user['password_hash'], self::ALGORITHM);
    }

    // Used both to upgrade an old hash at login and to set a chosen new password.
    public static function updatePassword(int $id, string $password): void
    {
        self::execute(
            'UPDATE users SET password_hash = :hash WHERE id = :id',
            ['hash' => password_hash($password, self::ALGORITHM), 'id' => $id]
        );
    }

    // ---------------------------------------------------------------------
    // Profile
    // ---------------------------------------------------------------------

    public static function updateUsername(int $id, string $username): void
    {
        self::executeUnique(
            'UPDATE users SET username = :username WHERE id = :id',
            ['username' => $username, 'id' => $id]
        );
    }

    /**
     * Postgres will not take PHP's false, which PDO binds as an empty string,
     * so the literal the database does understand is passed instead.
     */
    public static function setNotifyOnComment(int $id, bool $enabled): void
    {
        self::execute(
            'UPDATE users SET notify_on_comment = :enabled WHERE id = :id',
            ['enabled' => $enabled ? 'true' : 'false', 'id' => $id]
        );
    }

    // ---------------------------------------------------------------------
    // Email change
    //
    // The new address is parked in pending_email and only promoted to email
    // once the link sent to it comes back, so the account keeps answering at
    // the old address until the new one has been proved to exist.
    // ---------------------------------------------------------------------

    public static function startEmailChange(int $id, string $email, string $token): void
    {
        self::execute(
            "UPDATE users
                SET pending_email = :email,
                    email_change_token = :token,
                    email_change_expires_at = now() + interval '1 hour'
              WHERE id = :id",
            ['email' => $email, 'token' => $token, 'id' => $id]
        );
    }

    /**
     * Promotes the pending address, in one statement for the same reason
     * confirmPasswordChange() does: expiry and single use are settled by the
     * database.
     *
     * Throws DuplicateUserException when somebody else has registered the
     * address in the meantime — an hour is long enough for that to happen.
     */
    public static function confirmEmailChange(string $token): bool
    {
        $statement = self::executeUnique(
            'UPDATE users
                SET email = pending_email,
                    pending_email = NULL,
                    email_change_token = NULL,
                    email_change_expires_at = NULL
              WHERE email_change_token = :token
                AND email_change_expires_at > now()
                AND pending_email IS NOT NULL',
            ['token' => $token]
        );

        return $statement->rowCount() === 1;
    }

    public static function cancelEmailChange(int $id): void
    {
        self::execute(
            'UPDATE users
                SET pending_email = NULL,
                    email_change_token = NULL,
                    email_change_expires_at = NULL
              WHERE id = :id',
            ['id' => $id]
        );
    }

    // ---------------------------------------------------------------------
    // Password reset
    // ---------------------------------------------------------------------

    public static function startPasswordReset(int $id, string $token): void
    {
        self::execute(
            "UPDATE users
                SET reset_token = :token, reset_expires_at = now() + interval '1 hour'
              WHERE id = :id",
            ['token' => $token, 'id' => $id]
        );
    }

    public static function findByResetToken(string $token): ?array
    {
        return self::fetchOne(
            'SELECT * FROM users WHERE reset_token = :token AND reset_expires_at > now()',
            ['token' => $token]
        );
    }

    // ---------------------------------------------------------------------
    // Password change
    //
    // The chosen password is parked in pending_password_hash and only becomes
    // the real one once the link mailed to the account comes back, so until
    // then the old password is still the one that opens the account.
    // ---------------------------------------------------------------------

    public static function startPasswordChange(string $resetToken, string $password, string $token): bool
    {
        $statement = self::execute(
            "UPDATE users
                SET pending_password_hash = :hash,
                    password_change_token = :change_token,
                    password_change_expires_at = now() + interval '1 hour',
                    reset_token = NULL,
                    reset_expires_at = NULL
              WHERE reset_token = :reset_token AND reset_expires_at > now()",
            [
                'hash' => password_hash($password, self::ALGORITHM),
                'change_token' => $token,
                'reset_token' => $resetToken,
            ]
        );

        return $statement->rowCount() === 1;
    }

    public static function confirmPasswordChange(string $token): bool
    {
        $statement = self::execute(
            'UPDATE users
                SET password_hash = pending_password_hash,
                    pending_password_hash = NULL,
                    password_change_token = NULL,
                    password_change_expires_at = NULL
              WHERE password_change_token = :token
                AND password_change_expires_at > now()
                AND pending_password_hash IS NOT NULL',
            ['token' => $token]
        );

        return $statement->rowCount() === 1;
    }

    // So a sign-in with a password that is waiting to be confirmed can say so
    // instead of just calling it wrong.
    public static function isPendingPassword(?array $user, string $password): bool
    {
        if ($user === null || $user['pending_password_hash'] === null) {
            return false;
        }

        if (strtotime($user['password_change_expires_at']) <= time()) {
            return false;
        }

        return password_verify($password, $user['pending_password_hash']);
    }

    public static function newToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private static function duplicateField(string $message): ?string
    {
        return match (true) {
            str_contains($message, 'users_email_lower_idx') => 'email',
            str_contains($message, 'users_username_lower_idx') => 'username',
            default => null,
        };
    }

    private static function fetchOne(string $sql, array $params): ?array
    {
        return self::execute($sql, $params)->fetch() ?: null;
    }

    /**
     * For statements that can collide with the unique indexes on username and
     * email, turning the database's error code into one that names the field.
     */
    private static function executeUnique(string $sql, array $params): PDOStatement
    {
        try {
            return self::execute($sql, $params);
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $field = self::duplicateField($e->getMessage());

                if ($field !== null) {
                    throw new DuplicateUserException($field);
                }
            }

            throw $e;
        }
    }

    // Returns the statement, so callers that care can read rowCount().
    private static function execute(string $sql, array $params): PDOStatement
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement;
    }
}
