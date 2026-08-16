<?php

/**
 * This class is a model for the likes table.
 * A user either likes a photo or does not, so there is one operation: turn it
 * on if it is off, off if it is on.
 */

namespace app\models;

use app\core\Database;
use PDOStatement;

class Like
{
    // Returns the state the like ended up in.
    public static function toggle(int $photoId, int $userId): bool
    {
        $deleted = self::execute(
            'DELETE FROM likes WHERE photo_id = :photo AND user_id = :user',
            ['photo' => $photoId, 'user' => $userId]
        )->rowCount();

        if ($deleted > 0) {
            return false;
        }

        self::execute(
            'INSERT INTO likes (photo_id, user_id) VALUES (:photo, :user) ON CONFLICT DO NOTHING',
            ['photo' => $photoId, 'user' => $userId]
        );

        return true;
    }

    public static function countFor(int $photoId): int
    {
        return (int) self::execute(
            'SELECT count(*) FROM likes WHERE photo_id = :photo',
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
