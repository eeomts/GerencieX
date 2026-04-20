CREATE TABLE charges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    payment_type_id INT UNSIGNED NOT NULL,
    description  VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    payload TEXT NULL,        -- código pix ou linha digitável boleto
    status_id INT UNSIGNED NOT NULL default 1,
    paid_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    deleted TINYINT(1) NOT NULL DEFAULT 0
);