<?php
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /laundryr/dashboard.php"); exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($conn->real_escape_string($_POST['nama']));
    $username = trim($conn->real_escape_string($_POST['username']));
    $password = $_POST['password'];
    $konfirm  = $_POST['konfirmasi'];

    if (empty($nama) || empty($username) || empty($password)) {
        $error = "Semua field wajib diisi.";
    } elseif ($password !== $konfirm) {
        $error = "Password dan konfirmasi tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $cek = $conn->query("SELECT id FROM users WHERE username='$username'");
        if ($cek->num_rows > 0) {
            $error = "Username sudah digunakan, pilih yang lain.";
        } else {
            $hash = md5($password);
            $conn->query("INSERT INTO users (nama, username, password, role) VALUES ('$nama','$username','$hash','kasir')");
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
    <title>Daftar - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .register-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .register-logo {
            width: 60px; height: 60px;
            background: #1e293b;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.7rem; margin: 0 auto 1rem;
        }
        .form-control {
            border-radius: 8px; border-color: #e2e8f0;
            padding: 0.65rem 1rem; font-size: 0.9rem;
        }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .btn-register {
            background: #1e293b; border: none; border-radius: 8px;
            padding: 0.7rem; font-weight: 600; font-size: 0.9rem;
            transition: background .2s;
        }
        .btn-register:hover { background: #0f172a; }
        .input-group-text { border-color: #e2e8f0; background: #f8fafc; border-radius: 8px 0 0 8px; }
        .input-group .form-control { border-radius: 0 8px 8px 0; }
        .form-label { font-size: 0.82rem; font-weight: 500; color: #475569; margin-bottom: 4px; }
        .role-info { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.8rem; color: #64748b; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="text-center mb-4">
            <div class="register-logo"></div>
            <h5 class="fw-bold mb-1" style="color:#1e293b">Buat Akun</h5>
            <p class="text-muted small mb-0">Daftar untuk mengakses LaundryR</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:10px;font-size:.85rem;border:none">
                <i class="bi bi-exclamation-circle-fill"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:10px;font-size:.85rem;border:none">
                <i class="bi bi-check-circle-fill"></i> <?= $success ?>
                <a href="/laundryr/index.php" class="ms-auto text-success fw-600 text-decoration-none small">Login →</a>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="nama" class="form-control" placeholder="Nama lengkap Anda" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-at text-muted"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username untuk login" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill text-muted"></i></span>
                    <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password" required>
                </div>
            </div>
            <div class="role-info mb-4">
                <i class="bi bi-info-circle me-1"></i> Akun baru akan terdaftar sebagai <strong>Kasir</strong>. Hubungi admin untuk mengubah role.
            </div>
            <button type="submit" class="btn btn-register w-100 text-white">
                <i class="bi bi-person-plus me-1"></i> Daftar Sekarang
            </button>
        </form>

        <p class="text-center text-muted small mt-3 mb-0">
            Sudah punya akun? <a href="/laundryr/index.php" class="text-decoration-none fw-600" style="color:#2563eb">Masuk di sini</a>
        </p>
    </div>
</body>
</html>
