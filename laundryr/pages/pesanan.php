<?php
require_once '../config/db.php';
isLogin();
if ($_SESSION['user_role'] === 'owner') { header("Location: /laundryr/pages/laporan.php"); exit; }

$msg = '';

// Update status
if (isset($_POST['update_status'])) {
    $id     = (int)$_POST['id'];
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE pesanan SET status='$status' WHERE id=$id");
    $msg = ['type'=>'success','text'=>'Status pesanan berhasil diupdate.'];
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM pesanan WHERE id=$id");
    $msg = ['type'=>'warning','text'=>'Pesanan berhasil dihapus.'];
}

$filter = isset($_GET['status']) && $_GET['status'] !== '' ? "WHERE p.status='".$conn->real_escape_string($_GET['status'])."'" : '';
$pesanans = $conn->query("SELECT p.*, pl.nama AS nama_pelanggan, pl.telepon, o.nama_outlet FROM pesanan p LEFT JOIN pelanggan pl ON p.pelanggan_id=pl.id LEFT JOIN outlet o ON p.outlet_id=o.id $filter ORDER BY p.created_at DESC");

$total_menunggu = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status='menunggu'")->fetch_assoc()['c'];
$total_diproses = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status='diproses'")->fetch_assoc()['c'];
$total_selesai  = $conn->query("SELECT COUNT(*) as c FROM pesanan WHERE status='selesai'")->fetch_assoc()['c'];

$status_color = ['menunggu'=>'warning','diproses'=>'primary','selesai'=>'success','diambil'=>'secondary'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-basket2 me-2 text-primary"></i>Pesanan Masuk</p>
    </div>
    <div class="content-area">
        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg['type'] ?> d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-<?= $msg['type']==='success'?'check-circle':'exclamation-triangle' ?>-fill"></i>
                <?= $msg['text'] ?>
            </div>
        <?php endif; ?>

        <!-- Stat mini -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#fef9c3;width:44px;height:44px">
                            <i class="bi bi-clock" style="color:#ca8a04"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1"><?= $total_menunggu ?></div>
                            <div class="text-muted small">Menunggu</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#dbeafe;width:44px;height:44px">
                            <i class="bi bi-arrow-repeat" style="color:#2563eb"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1"><?= $total_diproses ?></div>
                            <div class="text-muted small">Diproses</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#dcfce7;width:44px;height:44px">
                            <i class="bi bi-check-circle" style="color:#16a34a"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold lh-1"><?= $total_selesai ?></div>
                            <div class="text-muted small">Selesai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header">
            <div>
                <h5>Daftar Pesanan</h5>
                <p>Pesanan dari pelanggan secara real-time</p>
            </div>
            <!-- Filter -->
            <div class="d-flex gap-2">
                <?php
                $statuses = [''=> 'Semua', 'menunggu'=>'Menunggu','diproses'=>'Diproses','selesai'=>'Selesai','diambil'=>'Diambil'];
                foreach ($statuses as $val => $label):
                    $active = ($_GET['status'] ?? '') === $val ? 'btn-dark' : 'btn-outline-secondary';
                ?>
                <a href="?status=<?= $val ?>" class="btn btn-sm <?= $active ?>" style="border-radius:8px;font-size:.78rem"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>Outlet</th>
                            <th>Berat</th>
                            <th>Catatan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pesanans->num_rows === 0): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada pesanan</td></tr>
                    <?php endif; ?>
                    <?php $no=1; while ($row=$pesanans->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="fw-500"><?= htmlspecialchars($row['nama_pelanggan']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($row['telepon'] ?? '') ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['jenis_layanan']) ?></td>
                            <td><?= htmlspecialchars($row['nama_outlet'] ?? '-') ?></td>
                            <td><?= $row['berat'] ?> kg</td>
                            <td class="text-muted small"><?= htmlspecialchars($row['catatan']) ?: '-' ?></td>
                            <td>
                                <span class="badge bg-<?= $status_color[$row['status']] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1"
                                    onclick="updateStatus(<?= $row['id'] ?>, '<?= $row['status'] ?>')" title="Update Status">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="?hapus=<?= $row['id'] ?><?= isset($_GET['status'])?'&status='.$_GET['status']:'' ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Hapus pesanan ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="status_id">
                <label class="form-label">Status Pesanan</label>
                <select name="status" id="status_val" class="form-select">
                    <option value="menunggu">Menunggu</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                    <option value="diambil">Diambil</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="update_status" class="btn btn-primary btn-sm px-4">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(id, status) {
    document.getElementById('status_id').value = id;
    document.getElementById('status_val').value = status;
    new bootstrap.Modal(document.getElementById('modalStatus')).show();
}
</script>
</body>
</html>
