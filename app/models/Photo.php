<?php

/**
 * This class is a model for the photos table.
 * It contains methods for interacting with photo data.
 */

namespace app\models;

use app\core\Database;
use PDOStatement;

class Photo
{
    private const SELECT =
        'SELECT p.id,
                p.filename,
                p.created_at,
                p.user_id  AS author_id,
                u.username AS author,
                (SELECT count(*) FROM likes    l WHERE l.photo_id = p.id) AS likes,
                (SELECT count(*) FROM comments c WHERE c.photo_id = p.id) AS comments,
                EXISTS (SELECT 1 FROM likes l
                         WHERE l.photo_id = p.id AND l.user_id = :viewer)  AS liked
           FROM photos p
           JOIN users u ON u.id = p.user_id';

    public static function paginate(int $limit, int $offset, ?int $viewerId): array
    {
        return self::execute(
            self::SELECT . '
          ORDER BY p.created_at DESC, p.id DESC
             LIMIT CAST(:limit AS integer) OFFSET CAST(:offset AS integer)',
            ['viewer' => $viewerId, 'limit' => $limit, 'offset' => $offset]
        )->fetchAll();
    }

    public static function findById(int $id, ?int $viewerId = null): ?array
    {
        return self::execute(
            self::SELECT . ' WHERE p.id = :id',
            ['viewer' => $viewerId, 'id' => $id]
        )->fetch() ?: null;
    }

    public static function count(): int
    {
        return (int) self::execute('SELECT count(*) FROM photos', [])->fetchColumn();
    }

    public static function deleteOwned(int $id, int $userId): ?string
    {
        $row = self::execute(
            'DELETE FROM photos WHERE id = :id AND user_id = :user RETURNING filename',
            ['id' => $id, 'user' => $userId]
        )->fetch();

        return $row === false ? null : $row['filename'];
    }

    private static function execute(string $sql, array $params): PDOStatement
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    public static function create(int $userId, string $filename): array
    {
        return self::execute(
            'INSERT INTO photos (user_id, filename)
                VALUES (:user, :filename)
            RETURNING id, filename, created_at, user_id AS author_id',
            ['user' => $userId, 'filename' => $filename]
        )->fetch();
    }

    // The editor's side panel: the signed-in user's own work, newest first.
    public static function panel(int $userId): array
    {
        return self::execute(
            'SELECT id, filename, created_at, user_id AS author_id
                FROM photos
                WHERE user_id = :user
            ORDER BY created_at DESC, id DESC',
            ['user' => $userId]
        )->fetchAll();
    }
}
