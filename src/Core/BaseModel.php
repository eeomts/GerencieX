<?php

declare(strict_types=1);

namespace Src\Core;

use PDO;
use Src\Database\Database;

abstract class BaseModel
{
    protected static function db(): PDO
    {
        return Database::connection();
    }
}
