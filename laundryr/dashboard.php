<?php
require_once 'config/db.php';
isLogin();

if ($_SESSION['user_role'] === 'owner') {
    header("Location: /laundryr/pages/owner_dashboard.php"); exit;
}

$role = $_SESSION['user_role'];
$total_users  = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_outlet = $conn->query("SELECT COUNT(*) as c FROM outlet")->fetch_assoc()['c'];
$total_member = $conn->query("SELECT COUNT(*) as c FROM member")->fetch_assoc()['c'];

// Member terbaru (untuk kasir & admin)
$member_terbaru = $conn->query("SELECT m.*, o.nama_outlet FROM member m LEFT JOIN outlet o ON m.outlet_id=o.id ORDER BY m.created_at DESC LIMIT 5");
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

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <?php if ($role === 'admin'): ?>
            <div class="col-md-4">
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
                        <a href="/laundryr/pages/users.php" class="text-decoration-none small" style="color:#2563eb">Lihat semua <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-<?= $role === 'admin' ? '4' : '6' ?>">
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
                        <a href="/laundryr/pages/outlet.php" class="text-decoration-none small text-success">Lihat semua <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-<?= $role === 'admin' ? '4' : '6' ?>">
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
                        <a href="/laundryr/pages/member.php" class="text-decoration-none small text-warning">Lihat semua <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Member Terbaru -->
        <div class="card card-table">
            <div class="card-body p-0">
                <div class="px-4 py-3 border-bottom" style="border-color:#e2e8f0!important">
                    <h6 class="mb-0 fw-600" style="font-size:.9rem"><i class="bi bi-clock-history me-2 text-muted"></i>Member Terbaru</h6>
                </div>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Outlet</th>
                            <th>Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no=1; while($row=$member_terbaru->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width:30px;height:30px;font-size:.72rem;background:#2563eb">
                                        <?= strtoupper(substr($row['nama'],0,1)) ?>
                                    </div>
                                    <?= htmlspecialchars($row['nama']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['telepon']) ?: '-' ?></td>
                            <td><?= htmlspecialchars($row['nama_outlet'] ?? '-') ?></td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
