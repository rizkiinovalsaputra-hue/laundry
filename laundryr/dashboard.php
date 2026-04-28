<?php
require_once 'config/db.php';
isLogin();

$total_users        = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_outlet       = $conn->query("SELECT COUNT(*) as c FROM outlet")->fetch_assoc()['c'];
$total_member       = $conn->query("SELECT COUNT(*) as c FROM member")->fetch_assoc()['c'];
$total_pesanan_baru = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status='menunggu'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
</head>
<body>
<?php require_once 'config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</p>
        <span class="text-muted small"><?= date('l, d F Y') ?></span>
    </div>
    <div class="content-area">
        <div class="row g-3 mb-4">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <div class="col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-4">
                        <div class="icon-box" style="background:#dbeafe">
                            <i class="bi bi-people-fill" style="color:#2563eb"></i>
                        </div>
                        <div>
                            <div class="fs-2 fw-bold text-dark lh-1"><?= $total_users ?></div>
                            <div class="text-muted small mt-1">Total Users</div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4">
                        <a href="/laundryr/pages/users.php" class="text-decoration-none small" style="color:#2563eb">
                            Lihat semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-4">
                        <div class="icon-box" style="background:#dcfce7">
                            <i class="bi bi-shop-window" style="color:#16a34a"></i>
                        </div>
                        <div>
                            <div class="fs-2 fw-bold text-dark lh-1"><?= $total_outlet ?></div>
                            <div class="text-muted small mt-1">Total Outlet</div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4">
                        <a href="/laundryr/pages/outlet.php" class="text-decoration-none small text-success">
                            Lihat semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-4">
                        <div class="icon-box" style="background:#fef3c7">
                            <i class="bi bi-person-badge-fill" style="color:#d97706"></i>
                        </div>
                        <div>
                            <div class="fs-2 fw-bold text-dark lh-1"><?= $total_member ?></div>
                            <div class="text-muted small mt-1">Total Member</div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4">
                        <a href="/laundryr/pages/member.php" class="text-decoration-none small text-warning">
                            Lihat semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-4">
                        <div class="icon-box" style="background:#fef9c3">
                            <i class="bi bi-basket2-fill" style="color:#ca8a04"></i>
                        </div>
                        <div>
                            <div class="fs-2 fw-bold text-dark lh-1"><?= $total_pesanan_baru ?></div>
                            <div class="text-muted small mt-1">Pesanan Baru</div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4">
                        <a href="/laundryr/pages/pesanan.php" class="text-decoration-none small" style="color:#ca8a04">
                            Lihat semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="card border-0 rounded-3" style="background:#1e293b;box-shadow:0 4px 20px rgba(0,0,0,0.12)">
            <div class="card-body p-4 text-white">
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size:2.5rem"></div>
                    <div>
                        <h6 class="fw-bold mb-1">Selamat datang, <?= htmlspecialchars($_SESSION['user_nama']) ?>!</h6>
                        <p class="mb-0 small opacity-75">Kelola data laundry Anda dengan mudah melalui panel ini.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
