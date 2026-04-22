create table payment_status (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       varchar(50),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    deleted    TINYINT(1) NOT NULL DEFAULT 0
);

insert into payment_status (name) values
('Pendente'),
('Pago'),
('Em atraso'),
('Vencido'),
('Cancelado'),
('Estornado'),
('Parcialmente pago');