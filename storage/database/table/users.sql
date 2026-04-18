CREATE TABLE users (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    apartment_id INT UNSIGNED,
    name         VARCHAR(120) NOT NULL,
    email        VARCHAR(180) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,    
    role         ENUM('admin','resident') NOT NULL DEFAULT 'resident',
    payment_type_id int UNSIGNED default 2,
    active       TINYINT(1)  NOT NULL DEFAULT 1,
    created_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME    NULL,
    deleted      TINYINT(1)  NOT NULL DEFAULT 0
);