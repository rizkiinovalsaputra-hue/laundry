<?php
session_start();
unset($_SESSION['pelanggan_id'], $_SESSION['pelanggan_nama']);
header("Location: /laundryr/login_pelanggan.php");
exit;
