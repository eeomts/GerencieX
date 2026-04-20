<?php

declare(strict_types=1);

namespace App\Models;

use Src\Core\BaseModel;

class UserType extends BaseModel
{
    public static function getAll(): array
    {
        return self::db()
            ->query('SELECT * FROM users_type WHERE deleted = 0')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users_type WHERE id = :id AND deleted = 0');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
