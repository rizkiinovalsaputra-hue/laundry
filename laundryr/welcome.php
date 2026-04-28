<?php
require_once 'config/db.php';
if (isset($_SESSION['user_id'])) { header("Location: /laundryr/dashboard.php"); exit; }
if (isset($_SESSION['pelanggan_id'])) { header("Location: /laundryr/pelanggan/dashboard.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LaundryR - Pilih Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            display: flex; flex-direction: column;
        }
        .split { display: flex; flex: 1; min-height: 100vh; }

        /* Sisi kiri - Admin */
        .side-admin {
            flex: 1;
            background: #1e293b;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 3rem 2rem;
            position: relative;
            overflow: hidden;
            transition: flex .3s ease;
        }
        .side-admin::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .side-admin .icon-wrap {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.08);
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; margin-bottom: 1.5rem;
        }
        .side-admin h3 { color: #fff; font-weight: 700; margin-bottom: .5rem; }
        .side-admin p  { color: rgba(255,255,255,.5); font-size: .9rem; text-align: center; margin-bottom: 2rem; }
        .btn-admin {
            background: #fff; color: #1e293b;
            border: none; border-radius: 10px;
            padding: .75rem 2.5rem; font-weight: 700;
            font-size: .95rem; text-decoration: none;
            transition: background .2s, transform .15s;
            display: inline-flex; align-items: center; gap: .5rem;
        }
        .btn-admin:hover { background: #f1f5f9; color: #1e293b; transform: translateY(-2px); }

        /* Sisi kanan - Pelanggan */
        .side-pelanggan {
            flex: 1;
            background: #f0f9ff;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 3rem 2rem;
            position: relative;
            overflow: hidden;
            transition: flex .3s ease;
        }
        .side-pelanggan::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230ea5e9' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .side-pelanggan .icon-wrap {
            width: 80px; height: 80px;
            background: #fff;
            border: 2px solid #bae6fd;
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem; margin-bottom: 1.5rem;
            box-shadow: 0 4px 16px rgba(14,165,233,.15);
        }
        .side-pelanggan h3 { color: #0c4a6e; font-weight: 700; margin-bottom: .5rem; }
        .side-pelanggan p  { color: #64748b; font-size: .9rem; text-align: center; margin-bottom: 2rem; }
        .btn-pelanggan {
            background: #0ea5e9; color: #fff;
            border: none; border-radius: 10px;
            padding: .75rem 2.5rem; font-weight: 700;
            font-size: .95rem; text-decoration: none;
            transition: background .2s, transform .15s;
            display: inline-flex; align-items: center; gap: .5rem;
        }
        .btn-pelanggan:hover { background: #0284c7; color: #fff; transform: translateY(-2px); }

        /* Divider tengah */
        .divider-center {
            width: 2px; background: #e2e8f0;
            position: relative; display: flex;
            align-items: center; justify-content: center;
        }
        .divider-center span {
            background: #fff; border: 2px solid #e2e8f0;
            border-radius: 50%; width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700; color: #94a3b8;
            position: absolute; z-index: 1;
        }

        /* Brand di atas */
        .brand-top {
            position: fixed; top: 1.25rem; left: 50%;
            transform: translateX(-50%);
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 50px; padding: .4rem 1.25rem;
            display: flex; align-items: center; gap: .5rem;
            font-weight: 700; font-size: .9rem; color: #1e293b;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            z-index: 10;
        }

        @media (max-width: 640px) {
            .split { flex-direction: column; }
            .divider-center { width: 100%; height: 2px; }
            .divider-center span { top: 50%; left: 50%; transform: translate(-50%,-50%); }
        }
    </style>
</head>
<body>

<div class="brand-top">
     LaundryR
</div>

<div class="split">
    <!-- Admin -->
    <div class="side-admin">
        <div class="icon-wrap">
            <i class="bi bi-shield-lock-fill text-white"></i>
        </div>
        <h3>Staff / Admin</h3>
        <p>Masuk sebagai admin atau kasir<br>untuk mengelola sistem laundry</p>
        <a href="/laundryr/index.php" class="btn-admin">
            <i class="bi bi-box-arrow-in-right"></i> Login Staff
        </a>
    </div>

    <!-- Divider -->
    <div class="divider-center">
        <span>atau</span>
    </div>

    <!-- Pelanggan -->
    <div class="side-pelanggan">
        <div class="icon-wrap">
            <i class="bi bi-person-fill" style="color:#0ea5e9;font-size:2.2rem"></i>
        </div>
        <h3>Pelanggan</h3>
        <p>Masuk sebagai pelanggan<br>untuk memesan layanan laundry</p>
        <a href="/laundryr/login_pelanggan.php" class="btn-pelanggan">
            <i class="bi bi-box-arrow-in-right"></i> Login Pelanggan
        </a>
        <a href="/laundryr/register_pelanggan.php" class="text-decoration-none mt-3" style="color:#0ea5e9;font-size:.85rem">
            Belum punya akun? <strong>Daftar di sini</strong>
        </a>
    </div>
</div>

</body>
</html>
