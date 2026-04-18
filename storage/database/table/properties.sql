CREATE TABLE properties (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    block     VARCHAR(10)  NOT NULL,
    number    VARCHAR(10)  NOT NULL,
    floor     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime,
    deleted tinyint not null default 0
);