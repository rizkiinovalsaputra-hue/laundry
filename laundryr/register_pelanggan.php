<?php
require_once 'config/db.php';

if (isset($_SESSION['pelanggan_id'])) {
    header("Location: /laundryr/pelanggan/dashboard.php"); exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($conn->real_escape_string($_POST['nama']));
    $username = trim($conn->real_escape_string($_POST['username']));
    $telepon  = trim($conn->real_escape_string($_POST['telepon']));
    $alamat   = trim($conn->real_escape_string($_POST['alamat']));
    $password = $_POST['password'];
    $konfirm  = $_POST['konfirmasi'];

    if (empty($nama) || empty($username) || empty($password)) {
        $error = "Nama, username, dan password wajib diisi.";
    } elseif ($password !== $konfirm) {
        $error = "Password dan konfirmasi tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $cek = $conn->query("SELECT id FROM pelanggan WHERE username='$username'");
        if ($cek->num_rows > 0) {
            $error = "Username sudah digunakan.";
        } else {
            $hash = md5($password);
            $conn->query("INSERT INTO pelanggan (nama, username, password, telepon, alamat) VALUES ('$nama','$username','$hash','$telepon','$alamat')");
            $success = "Akun berhasil dibuat! Silakan login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pelanggan - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif; padding: 2rem 0;
        }
        .card { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 24px rgba(0,0,0,0.08); width: 100%; max-width: 420px; }
        .logo-box { width: 60px; height: 60px; background: #0ea5e9; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.7rem; margin: 0 auto 1rem; }
        .form-control { border-radius: 8px; border-color: #e2e8f0; font-size: .9rem; padding: .65rem 1rem; }
        .form-control:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.1); }
        .input-group-text { border-color: #e2e8f0; background: #f8fafc; border-radius: 8px 0 0 8px; }
        .input-group .form-control { border-radius: 0 8px 8px 0; }
        .btn-daftar { background: #0ea5e9; border: none; border-radius: 8px; font-weight: 600; font-size: .9rem; padding: .7rem; transition: background .2s; }
        .btn-daftar:hover { background: #0284c7; }
        .form-label { font-size: .82rem; font-weight: 500; color: #475569; margin-bottom: 4px; }
    </style>
</head>
<body>
<div class="card p-4">
    <div class="text-center mb-4">
        <div class="logo-box"></div>
        <h5 class="fw-bold mb-1" style="color:#0c4a6e">Daftar Pelanggan</h5>
        <p class="text-muted small mb-0">Buat akun untuk memesan laundry</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:10px;font-size:.85rem;border:none">
            <i class="bi bi-exclamation-circle-fill"></i> <?= $error ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:10px;font-size:.85rem;border:none">
            <i class="bi bi-check-circle-fill"></i> <?= $success ?>
            <a href="/laundryr/login_pelanggan.php" class="ms-auto text-success fw-600 text-decoration-none small">Login →</a>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required autofocus>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Username <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-at text-muted"></i></span>
                <input type="text" name="username" class="form-control" placeholder="Username untuk login" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Nomor Telepon</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone text-muted"></i></span>
                <input type="text" name="telepon" class="form-control" placeholder="Nomor telepon" value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
                <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-daftar w-100 text-white">
            <i class="bi bi-person-check me-1"></i> Daftar Sekarang
        </button>
    </form>

    <p class="text-center text-muted small mt-3 mb-0">
        Sudah punya akun? <a href="/laundryr/login_pelanggan.php" class="text-decoration-none fw-600" style="color:#0ea5e9">Masuk di sini</a>
    </p>
</div>
</body>
</html>
