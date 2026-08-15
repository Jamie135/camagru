<?php

/**
 * Thrown when a signup collides with an existing account.
 */

namespace app\models;

use RuntimeException;

class DuplicateUserException extends RuntimeException
{
    public function __construct(public readonly string $field)
    {
        parent::__construct("A user with this {$field} already exists.");
    }
}
