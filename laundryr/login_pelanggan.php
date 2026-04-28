<?php
require_once 'config/db.php';

if (isset($_SESSION['pelanggan_id'])) {
    header("Location: /laundryr/pelanggan/dashboard.php"); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']);
    $result = $conn->query("SELECT * FROM pelanggan WHERE username='$username' AND password='$password'");
    if ($result->num_rows > 0) {
        $p = $result->fetch_assoc();
        $_SESSION['pelanggan_id']   = $p['id'];
        $_SESSION['pelanggan_nama'] = $p['nama'];
        header("Location: /laundryr/pelanggan/dashboard.php"); exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Pelanggan - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f0f9ff; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: #fff; border-radius: 16px;
            padding: 2.5rem 2rem; width: 100%; max-width: 380px;
            box-shadow: 0 4px 24px rgba(14,165,233,0.1); border: 1px solid #bae6fd;
        }
        .login-logo {
            width: 60px; height: 60px; background: #0ea5e9;
            border-radius: 14px; display: flex; align-items: center;
            justify-content: center; font-size: 1.7rem; margin: 0 auto 1rem;
            box-shadow: 0 4px 14px rgba(14,165,233,.3);
        }
        .form-control { border-radius: 8px; border-color: #e0f2fe; background: #f0f9ff; padding: .65rem 1rem; font-size: .9rem; }
        .form-control:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); background: #fff; }
        .input-group-text { border-color: #e0f2fe; background: #f0f9ff; border-radius: 8px 0 0 8px; color: #7dd3fc; }
        .input-group .form-control { border-radius: 0 8px 8px 0; }
        .btn-login { background: #0ea5e9; border: none; border-radius: 8px; padding: .7rem; font-weight: 600; font-size: .9rem; transition: background .2s; box-shadow: 0 4px 14px rgba(14,165,233,.3); }
        .btn-login:hover { background: #0284c7; }
        .form-label { font-size: .82rem; font-weight: 500; color: #475569; margin-bottom: 4px; }
        .alert { border-radius: 8px; border: none; font-size: .85rem; }
        .badge-role { display: inline-flex; align-items: center; gap: .35rem; background: #e0f2fe; border: 1px solid #bae6fd; color: #0284c7; border-radius: 50px; padding: .25rem .75rem; font-size: .72rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h5 class="fw-bold mb-1" style="color:#0c4a6e">LaundryR</h5>
            <div class="badge-role mt-1"><i class="bi bi-person-fill"></i> Portal Pelanggan</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3">
                <i class="bi bi-exclamation-circle-fill"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username Anda" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password Anda" required>
                </div>
            </div>
            <button type="submit" class="btn btn-login w-100 text-white">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>

        <hr class="my-3" style="border-color:#e0f2fe">
        <p class="text-center mb-1" style="font-size:.82rem;color:#94a3b8">
            Belum punya akun? <a href="/laundryr/register_pelanggan.php" class="text-decoration-none fw-600" style="color:#0ea5e9">Daftar sekarang</a>
        </p>
        <p class="text-center mb-0" style="font-size:.82rem;color:#94a3b8">
            Anda staff? <a href="/laundryr/index.php" class="text-decoration-none fw-600" style="color:#1e293b">Login Staff</a>
        </p>
    </div>
</body>
</html>
