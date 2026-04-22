<?php

declare(strict_types=1);

namespace App\Models;

use Src\Core\BaseModel;

class Condo extends BaseModel
{
    public static function getAll(): array
    {
        return self::db()
            ->query('SELECT * FROM condos WHERE deleted = 0 ORDER BY name')
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM condos WHERE id = :id AND deleted = 0');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByToken(string $token): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM condos WHERE access_token = :token');
        $stmt->execute([':token' => $token]);
        return $stmt->fetch() ?: null;
    }
}

