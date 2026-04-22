<?php

declare(strict_types=1);

namespace App\Models;

use Src\Core\BaseModel;

class Property extends BaseModel
{
    public static function getAll(): array
    {
        return self::db()->query('
            SELECT
                properties.id,
                properties.condo_id,
                properties.block,
                properties.number,
                properties.floor,
                properties.created_at,
                properties.updated_at,
                properties.deleted,
                condos.name AS condo_name
            FROM properties
            LEFT JOIN condos ON condos.id = properties.condo_id AND condos.deleted = 0
            WHERE properties.deleted = 0
            ORDER BY condos.name, properties.block, properties.number
        ')->fetchAll();
    }

    public static function getByCondo(int $condoId): array
    {
        $stmt = self::db()->prepare('
            SELECT
                properties.id,
                properties.condo_id,
                properties.block,
                properties.number,
                properties.floor,
                properties.created_at,
                properties.updated_at,
                properties.deleted,
                condos.name AS condo_name
            FROM properties
            LEFT JOIN condos ON condos.id = properties.condo_id AND condos.deleted = 0
            WHERE properties.deleted = 0 AND properties.condo_id = :condo_id
            ORDER BY properties.block, properties.number
        ');
        $stmt->execute([':condo_id' => $condoId]);
        return $stmt->fetchAll();
    }

    public static function getByUser(int $userId): array
    {
        $stmt = self::db()->prepare('
            SELECT
                properties.id,
                properties.condo_id,
                properties.block,
                properties.number,
                properties.floor,
                properties.created_at,
                properties.updated_at,
                properties.deleted,
                condos.name AS condo_name
            FROM properties
            INNER JOIN property_users ON property_users.property_id = properties.id
            LEFT JOIN condos ON condos.id = properties.condo_id AND condos.deleted = 0
            WHERE properties.deleted = 0 AND property_users.user_id = :user_id
            ORDER BY condos.name, properties.block, properties.number
        ');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function getWithUsers(int $propertyId): ?array
    {
        $stmt = self::db()->prepare('
            SELECT
                properties.id,
                properties.condo_id,
                properties.block,
                properties.number,
                properties.floor,
                properties.created_at,
                properties.updated_at,
                properties.deleted,
                condos.name AS condo_name
            FROM properties
            LEFT JOIN condos ON condos.id = properties.condo_id AND condos.deleted = 0
            WHERE properties.id = :id AND properties.deleted = 0
        ');
        $stmt->execute([':id' => $propertyId]);
        $property = $stmt->fetch();

        if ($property === null) {
            return null;
        }

        $property['users'] = User::getByProperty((int) $propertyId);

        return $property;
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('
            SELECT
                properties.id,
                properties.condo_id,
                properties.block,
                properties.number,
                properties.floor,
                properties.created_at,
                properties.updated_at,
                properties.deleted,
                condos.name AS condo_name
            FROM properties
            LEFT JOIN condos ON condos.id = properties.condo_id AND condos.deleted = 0
            WHERE properties.id = :id AND properties.deleted = 0
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
