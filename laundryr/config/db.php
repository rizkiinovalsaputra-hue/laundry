<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'R_laundry';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8");

function isLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /laundryr/index.php");
        exit;
    }
}

function isPelanggan() {
    if (!isset($_SESSION['pelanggan_id'])) {
        header("Location: /laundryr/login_pelanggan.php");
        exit;
    }
}
