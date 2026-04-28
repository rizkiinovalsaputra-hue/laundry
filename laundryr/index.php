<?php
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /laundryr/dashboard.php"); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']);
    $result = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: /laundryr/dashboard.php"); exit;
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
    <title>Login Staff - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: #fff; border-radius: 16px;
            padding: 2.5rem 2rem; width: 100%; max-width: 380px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;
        }
        .login-logo {
            width: 60px; height: 60px; background: #1e293b;
            border-radius: 14px; display: flex; align-items: center;
            justify-content: center; font-size: 1.7rem; margin: 0 auto 1rem;
        }
        .form-control { border-radius: 8px; border-color: #e2e8f0; padding: .65rem 1rem; font-size: .9rem; }
        .form-control:focus { border-color: #1e293b; box-shadow: 0 0 0 3px rgba(30,41,59,.1); }
        .input-group-text { border-color: #e2e8f0; background: #f8fafc; border-radius: 8px 0 0 8px; }
        .input-group .form-control { border-radius: 0 8px 8px 0; }
        .btn-login { background: #1e293b; border: none; border-radius: 8px; padding: .7rem; font-weight: 600; font-size: .9rem; transition: background .2s; }
        .btn-login:hover { background: #0f172a; }
        .form-label { font-size: .82rem; font-weight: 500; color: #475569; margin-bottom: 4px; }
        .alert { border-radius: 8px; border: none; font-size: .85rem; }
        .badge-role { display: inline-flex; align-items: center; gap: .35rem; background: #f1f5f9; border: 1px solid #e2e8f0; color: #64748b; border-radius: 50px; padding: .25rem .75rem; font-size: .72rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h5 class="fw-bold mb-1" style="color:#1e293b">LaundryR</h5>
            <div class="badge-role mt-1"><i class="bi bi-shield-lock-fill"></i> Login Staff / Admin</div>
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
                    <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username staff" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-login w-100 text-white">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Panel
            </button>
        </form>

        <hr class="my-3" style="border-color:#e2e8f0">
        <p class="text-center mb-1" style="font-size:.82rem;color:#94a3b8">
            Belum punya akun? <a href="/laundryr/register.php" class="text-decoration-none fw-600" style="color:#1e293b">Daftar di sini</a>
        </p>
        <p class="text-center mb-0" style="font-size:.82rem;color:#94a3b8">
            Anda pelanggan? <a href="/laundryr/login_pelanggan.php" class="text-decoration-none fw-600" style="color:#0ea5e9">Login Pelanggan</a>
        </p>
    </div>
</body>
</html>
