<?php
// Script untuk memastikan tabel ada
require_once '../config/db.php';

// Create tb_paket table if not exists
$sql_paket = "CREATE TABLE IF NOT EXISTS tb_paket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    outlet_id INT NOT NULL,
    jenis ENUM('kiloan','selimut','bed_cover','kaos','lain') NOT NULL,
    nama_paket VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outlet_id) REFERENCES outlet(id) ON DELETE CASCADE
)";

// Create tb_transaksi table if not exists
$sql_transaksi = "CREATE TABLE IF NOT EXISTS tb_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    outlet_id INT NOT NULL,
    kode_invoice VARCHAR(100) NOT NULL UNIQUE,
    member_id INT,
    tgl DATE NOT NULL,
    batas_waktu DATETIME NOT NULL,
    tgl_bayar DATETIME NULL,
    biaya_tambahan DECIMAL(10,2) DEFAULT 0,
    diskon DECIMAL(10,2) DEFAULT 0,
    pajak DECIMAL(10,2) DEFAULT 0,
    status ENUM('baru','proses','selesai','diambil') DEFAULT 'baru',
    dibayar ENUM('belum_bayar','dibayar') DEFAULT 'belum_bayar',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outlet_id) REFERENCES outlet(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES member(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Create tb_detail_transaksi table if not exists
$sql_detail = "CREATE TABLE IF NOT EXISTS tb_detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    paket_id INT NOT NULL,
    qty DECIMAL(8,2) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaksi_id) REFERENCES tb_transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (paket_id) REFERENCES tb_paket(id) ON DELETE CASCADE
)";

// Execute queries
$conn->query($sql_paket);
$conn->query($sql_transaksi);
$conn->query($sql_detail);

echo "Tabel berhasil dibuat/diperbarui!";
?>