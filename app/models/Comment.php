<?php

/**
 * This class is a model for the comments table.
 * Comments are read a whole page of photos at a time, so the gallery does not
 * run one query per card.
 */

namespace app\models;

use app\core\Database;
use PDOStatement;

class Comment
{
    private const SELECT =
        'SELECT c.id,
                c.photo_id,
                c.body,
                c.created_at,
                u.username AS author
           FROM comments c
           JOIN users u ON u.id = c.user_id';

    public static function create(int $photoId, int $userId, string $body): array
    {
        return self::execute(
            'INSERT INTO comments (photo_id, user_id, body)
                  VALUES (:photo, :user, :body)
               RETURNING id,
                         photo_id,
                         body,
                         created_at,
                         (SELECT username FROM users WHERE id = user_id) AS author',
            ['photo' => $photoId, 'user' => $userId, 'body' => $body]
        )->fetch();
    }

    public static function forPhoto(int $photoId): array
    {
        return self::execute(
            self::SELECT . ' WHERE c.photo_id = :photo ORDER BY c.created_at, c.id',
            ['photo' => $photoId]
        )->fetchAll();
    }

    public static function forPhotos(array $photoIds): array
    {
        if ($photoIds === []) {
            return [];
        }

        $rows = self::execute(
            self::SELECT . ' WHERE c.photo_id = ANY (CAST(:ids AS integer[]))
                          ORDER BY c.created_at, c.id',
            ['ids' => '{' . implode(',', array_map('intval', $photoIds)) . '}']
        )->fetchAll();

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['photo_id']][] = $row;
        }

        return $grouped;
    }

    public static function countFor(int $photoId): int
    {
        return (int) self::execute(
            'SELECT count(*) FROM comments WHERE photo_id = :photo',
            ['photo' => $photoId]
        )->fetchColumn();
    }

    private static function execute(string $sql, array $params): PDOStatement
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement;
    }
}
