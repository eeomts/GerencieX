create table expenses_category (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       varchar(50),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    deleted    TINYINT(1) NOT NULL DEFAULT 0
);