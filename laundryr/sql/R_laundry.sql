CREATE DATABASE IF NOT EXISTS R_laundry;
USE R_laundry;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','kasir') DEFAULT 'kasir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS outlet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_outlet VARCHAR(100) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS member (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(20),
    alamat TEXT,
    outlet_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outlet_id) REFERENCES outlet(id) ON DELETE SET NULL
);

INSERT INTO users (nama, username, password, role) VALUES ('Administrator', 'admin', MD5('admin123'), 'admin');
