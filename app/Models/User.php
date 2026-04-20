<?php

declare(strict_types=1);

namespace App\Models;

use Src\Core\BaseModel;

class User extends BaseModel
{
    public static function getAll(): array
    {
        return self::db()->query('
            SELECT
                users.id,
                users.name,
                users.email,
                users.user_type,
                users.payment_type_id,
                users.active,
                users.created_at,
                users.updated_at,
                users.deleted,
                users_type.name AS user_type_name
            FROM users
            LEFT JOIN users_type ON users_type.id = users.user_type AND users_type.deleted = 0
            WHERE users.deleted = 0
        ')->fetchAll();
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE email = :email AND deleted = 0 AND active = 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public static function getByUserType(int $userType): array
    {
        $stmt = self::db()->prepare('
            SELECT
                users.id,
                users.name,
                users.email,
                users.user_type,
                users.payment_type_id,
                users.active,
                users.created_at,
                users.updated_at,
                users.deleted,
                users_type.name AS user_type_name
            FROM users
            LEFT JOIN users_type ON users_type.id = users.user_type AND users_type.deleted = 0
            WHERE users.deleted = 0 AND users.user_type = :user_type
        ');
        $stmt->execute([':user_type' => $userType]);
        return $stmt->fetchAll();
    }

    public static function getByProperty(int $propertyId): array
    {
        $stmt = self::db()->prepare('
            SELECT
                users.id,
                users.name,
                users.email,
                users.user_type,
                users.active,
                users.created_at,
                users_type.name AS user_type_name
            FROM users
            INNER JOIN property_users ON property_users.user_id = users.id
            LEFT JOIN users_type ON users_type.id = users.user_type AND users_type.deleted = 0
            WHERE users.deleted = 0 AND property_users.property_id = :property_id
            ORDER BY users.name
        ');
        $stmt->execute([':property_id' => $propertyId]);
        return $stmt->fetchAll();
    }

    public static function getByCondo(int $condoId): array
    {
        $stmt = self::db()->prepare('
            SELECT DISTINCT
                users.id,
                users.name,
                users.email,
                users.user_type,
                users.active,
                users.created_at,
                users_type.name AS user_type_name,
                properties.block,
                properties.number AS apartment_number,
                condos.name AS condo_name
            FROM users
            INNER JOIN property_users ON property_users.user_id = users.id
            INNER JOIN properties ON properties.id = property_users.property_id AND properties.deleted = 0
            LEFT JOIN users_type ON users_type.id = users.user_type AND users_type.deleted = 0
            LEFT JOIN condos ON condos.id = properties.condo_id AND condos.deleted = 0
            WHERE users.deleted = 0 AND properties.condo_id = :condo_id
            ORDER BY properties.block, properties.number, users.name
        ');
        $stmt->execute([':condo_id' => $condoId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('
            SELECT
                users.id,
                users.name,
                users.email,
                users.user_type,
                users.payment_type_id,
                users.active,
                users.created_at,
                users.updated_at,
                users.deleted,
                users_type.name AS user_type_name
            FROM users
            LEFT JOIN users_type ON users_type.id = users.user_type AND users_type.deleted = 0
            WHERE users.id = :id AND users.deleted = 0
        ');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
