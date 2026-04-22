CREATE TABLE property_users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NULL,
    deleted    TINYINT(1)   NOT NULL DEFAULT 0
    
);
