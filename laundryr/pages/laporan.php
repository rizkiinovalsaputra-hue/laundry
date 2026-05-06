<?php
require_once '../config/db.php';
isLogin();

if ($_SESSION['user_role'] !== 'owner') {
    header("Location: /laundryr/dashboard.php"); exit;
}

$total_pesanan   = $conn->query("SELECT COUNT(*) as c FROM pesanan")->fetch_assoc()['c'];
$total_selesai   = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status='selesai' OR status='diambil'")->fetch_assoc()['c'];
$total_proses    = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status='diproses'")->fetch_assoc()['c'];
$total_menunggu  = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status='menunggu'")->fetch_assoc()['c'];
$total_pelanggan = $conn->query("SELECT COUNT(*) as c FROM pelanggan")->fetch_assoc()['c'];
$total_member    = $conn->query("SELECT COUNT(*) as c FROM member")->fetch_assoc()['c'];
$total_outlet    = $conn->query("SELECT COUNT(*) as c FROM outlet")->fetch_assoc()['c'];
$total_users     = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];

// Pesanan per outlet
$per_outlet = $conn->query("SELECT o.nama_outlet, COUNT(p.id) as total FROM pesanan p LEFT JOIN outlet o ON p.outlet_id=o.id GROUP BY p.outlet_id ORDER BY total DESC");

// Pesanan terbaru
$pesanan_baru = $conn->query("SELECT p.*, pl.nama AS nama_pelanggan, o.nama_outlet FROM pesanan p LEFT JOIN pelanggan pl ON p.pelanggan_id=pl.id LEFT JOIN outlet o ON p.outlet_id=o.id ORDER BY p.created_at DESC LIMIT 10");

$status_color = ['menunggu'=>'warning','diproses'=>'primary','selesai'=>'success','diambil'=>'secondary'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Laporan & Ringkasan</p>
        <span class="text-muted small"><?= date('d F Y') ?></span>
    </div>
    <div class="content-area">

        <!-- Stat pesanan -->
        <p class="text-muted small fw-600 mb-2" style="text-transform:uppercase;letter-spacing:.05em">Ringkasan Pesanan</p>
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#f1f5f9;width:44px;height:44px"><i class="bi bi-basket2" style="color:#64748b"></i></div>
                        <div><div class="fs-4 fw-bold lh-1"><?= $total_pesanan ?></div><div class="text-muted small">Total Pesanan</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#fef9c3;width:44px;height:44px"><i class="bi bi-clock" style="color:#ca8a04"></i></div>
                        <div><div class="fs-4 fw-bold lh-1"><?= $total_menunggu ?></div><div class="text-muted small">Menunggu</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#dbeafe;width:44px;height:44px"><i class="bi bi-arrow-repeat" style="color:#2563eb"></i></div>
                        <div><div class="fs-4 fw-bold lh-1"><?= $total_proses ?></div><div class="text-muted small">Diproses</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#dcfce7;width:44px;height:44px"><i class="bi bi-check-circle" style="color:#16a34a"></i></div>
                        <div><div class="fs-4 fw-bold lh-1"><?= $total_selesai ?></div><div class="text-muted small">Selesai</div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat umum -->
        <p class="text-muted small fw-600 mb-2" style="text-transform:uppercase;letter-spacing:.05em">Data Umum</p>
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#f3e8ff;width:44px;height:44px"><i class="bi bi-person-heart" style="color:#7c3aed"></i></div>
                        <div><div class="fs-4 fw-bold lh-1"><?= $total_pelanggan ?></div><div class="text-muted small">Pelanggan</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#fef3c7;width:44px;height:44px"><i class="bi bi-person-badge" style="color:#d97706"></i></div>
                        <div><div class="fs-4 fw-bold lh-1"><?= $total_member ?></div><div class="text-muted small">Member</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#dcfce7;width:44px;height:44px"><i class="bi bi-shop" style="color:#16a34a"></i></div>
                        <div><div class="fs-4 fw-bold lh-1"><?= $total_outlet ?></div><div class="text-muted small">Outlet</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#dbeafe;width:44px;height:44px"><i class="bi bi-people" style="color:#2563eb"></i></div>
                        <div><div class="fs-4 fw-bold lh-1"><?= $total_users ?></div><div class="text-muted small">Staff</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Pesanan per outlet -->
            <div class="col-md-4">
                <div class="card card-table h-100">
                    <div class="card-body p-0">
                        <div class="px-4 py-3 border-bottom" style="border-color:#e2e8f0!important">
                            <h6 class="mb-0 fw-600" style="font-size:.9rem">Pesanan per Outlet</h6>
                        </div>
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Outlet</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                            <?php while ($r = $per_outlet->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nama_outlet'] ?? 'Tidak ada outlet') ?></td>
                                    <td class="text-end"><span class="badge bg-primary"><?= $r['total'] ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pesanan terbaru -->
            <div class="col-md-8">
                <div class="card card-table">
                    <div class="card-body p-0">
                        <div class="px-4 py-3 border-bottom" style="border-color:#e2e8f0!important">
                            <h6 class="mb-0 fw-600" style="font-size:.9rem">10 Pesanan Terbaru</h6>
                        </div>
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Pelanggan</th><th>Layanan</th><th>Outlet</th><th>Status</th><th>Tanggal</th></tr></thead>
                            <tbody>
                            <?php while ($r = $pesanan_baru->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nama_pelanggan']) ?></td>
                                    <td><?= htmlspecialchars($r['jenis_layanan']) ?></td>
                                    <td><?= htmlspecialchars($r['nama_outlet'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= $status_color[$r['status']] ?>"><?= ucfirst($r['status']) ?></span></td>
                                    <td class="text-muted small"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
