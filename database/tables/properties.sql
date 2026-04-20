CREATE TABLE properties (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    condo_id   INT UNSIGNED NOT NULL,
    block      VARCHAR(10)  NOT NULL,
    number     VARCHAR(10)  NOT NULL,
    floor      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NULL,
    deleted    TINYINT(1)   NOT NULL DEFAULT 0
    
);
