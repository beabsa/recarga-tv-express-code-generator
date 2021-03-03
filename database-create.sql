/*
MySQL:
CREATE TABLE serial_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    serial CHAR(16) UNIQUE NOT NULL,
    user_email VARCHAR(255) DEFAULT NULL,
    product ENUM('mensal', 'anual', 'mensal-mc', 'anual-mc') DEFAULT NULL
);*/

-- SQLite:
CREATE TABLE serial_codes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    serial TEXT UNIQUE NOT NULL,
    user_email TEXT DEFAULT NULL,
    product TEXT DEFAULT NULL
);