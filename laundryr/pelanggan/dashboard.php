<?php
require_once '../config/db.php';
isPelanggan();

$pid = (int)$_SESSION['pelanggan_id'];
$msg = '';

// Buat pesanan
if (isset($_POST['pesan'])) {
    $layanan = $conn->real_escape_string($_POST['jenis_layanan']);
    $berat   = (float)$_POST['berat'];
    $catatan = $conn->real_escape_string($_POST['catatan']);
    $outlet  = (int)$_POST['outlet_id'];
    $conn->query("INSERT INTO pesanan (pelanggan_id, outlet_id, jenis_layanan, berat, catatan) VALUES ($pid, $outlet, '$layanan', $berat, '$catatan')");
    $msg = ['type'=>'success','text'=>'Pesanan berhasil dikirim! Kami akan segera memproses laundry Anda.'];
}

$outlets  = $conn->query("SELECT id, nama_outlet FROM outlet ORDER BY nama_outlet");
$pesanans = $conn->query("SELECT p.*, o.nama_outlet FROM pesanan p LEFT JOIN outlet o ON p.outlet_id=o.id WHERE p.pelanggan_id=$pid ORDER BY p.created_at DESC");

$status_color = ['menunggu'=>'warning','diproses'=>'primary','selesai'=>'success','diambil'=>'secondary'];
$status_icon  = ['menunggu'=>'clock','diproses'=>'arrow-repeat','selesai'=>'check-circle','diambil'=>'bag-check'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Pelanggan - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        .topnav {
            background: #0ea5e9; padding: .75rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 99;
        }
        .topnav .brand { color: #fff; font-weight: 700; font-size: 1rem; text-decoration: none; }
        .topnav .user  { color: rgba(255,255,255,.9); font-size: .85rem; }
        .content { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .card { border: none; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
        .card-header { background: #fff; border-bottom: 1px solid #e2e8f0; border-radius: 14px 14px 0 0 !important; padding: 1rem 1.25rem; }
        .card-header h6 { margin: 0; font-weight: 600; color: #1e293b; }
        .form-label { font-size: .82rem; font-weight: 500; color: #475569; margin-bottom: 4px; }
        .form-control, .form-select { border-radius: 8px; border-color: #e2e8f0; font-size: .875rem; }
        .form-control:focus, .form-select:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.1); }
        .btn-pesan { background: #0ea5e9; border: none; border-radius: 8px; font-weight: 600; font-size: .875rem; transition: background .2s; }
        .btn-pesan:hover { background: #0284c7; }
        .table thead th { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #64748b; background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: .75rem 1rem; }
        .table tbody td { padding: .75rem 1rem; vertical-align: middle; font-size: .875rem; color: #334155; }
        .badge { font-size: .72rem; font-weight: 500; padding: .35em .7em; border-radius: 6px; }
        .alert { border-radius: 10px; border: none; font-size: .875rem; }
        .layanan-option { cursor: pointer; }
        .layanan-option input:checked + label { border-color: #0ea5e9; background: #f0f9ff; }
        .layanan-option label { border: 2px solid #e2e8f0; border-radius: 10px; padding: .6rem 1rem; cursor: pointer; transition: all .15s; display: block; font-size: .875rem; }
        .layanan-option label:hover { border-color: #0ea5e9; }
    </style>
</head>
<body>

<nav class="topnav">
    <a href="#" class="brand"> LaundryR</a>
    <a href="#" class="brand"> LaundryR</a>
    <div class="d-flex align-items-center gap-3">
        <span class="user"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['pelanggan_nama']) ?></span>
        <a href="/laundryr/logout_pelanggan.php" class="btn btn-sm text-white" style="background:rgba(255,255,255,.2);border-radius:8px;font-size:.8rem">
            <i class="bi bi-box-arrow-right"></i> Keluar
        </a>
    </div>
</nav>

<div class="content">
    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg['type'] ?> d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-check-circle-fill"></i> <?= $msg['text'] ?>
        </div>
    <?php endif; ?>

    <!-- Form Pesan -->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-basket2 text-primary"></i>
            <h6>Buat Pesanan Baru</h6>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Jenis Layanan</label>
                    <div class="row g-2">
                        <?php
                        $layanan_list = ['Cuci & Setrika','Cuci Kering','Setrika Saja','Laundry Kiloan','Dry Cleaning'];
                        foreach ($layanan_list as $l):
                        ?>
                        <div class="col-6 col-md-4 layanan-option">
                            <input type="radio" name="jenis_layanan" id="l_<?= md5($l) ?>" value="<?= $l ?>" class="d-none" required>
                            <label for="l_<?= md5($l) ?>">
                                <i class="bi bi-check2 me-1 text-primary d-none check-icon"></i><?= $l ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Outlet</label>
                        <select name="outlet_id" class="form-select" required>
                            <option value="">-- Pilih Outlet --</option>
                            <?php while ($o = $outlets->fetch_assoc()): ?>
                                <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama_outlet']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estimasi Berat (kg)</label>
                        <input type="number" name="berat" class="form-control" placeholder="Contoh: 2.5" step="0.5" min="0.5" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Catatan Tambahan</label>
                    <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: pakaian putih dipisah, dll."></textarea>
                </div>
                <button type="submit" name="pesan" class="btn btn-pesan text-white px-4">
                    <i class="bi bi-send me-1"></i> Kirim Pesanan
                </button>
            </form>
        </div>
    </div>

    <!-- Riwayat Pesanan -->
    <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-secondary"></i>
            <h6>Riwayat Pesanan Saya</h6>
        </div>
        <div class="card-body p-0">
            <?php if ($pesanans->num_rows === 0): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    Belum ada pesanan
                </div>
            <?php else: ?>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Layanan</th>
                        <th>Outlet</th>
                        <th>Berat</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; while ($row=$pesanans->fetch_assoc()): $sc=$status_color[$row['status']]; $si=$status_icon[$row['status']]; ?>
                    <tr>
                        <td class="text-muted"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['jenis_layanan']) ?></td>
                        <td><?= htmlspecialchars($row['nama_outlet'] ?? '-') ?></td>
                        <td><?= $row['berat'] ?> kg</td>
                        <td>
                            <span class="badge bg-<?= $sc ?>">
                                <i class="bi bi-<?= $si ?> me-1"></i><?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.layanan-option input').forEach(input => {
    input.addEventListener('change', () => {
        document.querySelectorAll('.layanan-option label').forEach(l => l.style.borderColor = '#e2e8f0');
        document.querySelectorAll('.layanan-option label').forEach(l => l.style.background = '');
        input.nextElementSibling.style.borderColor = '#0ea5e9';
        input.nextElementSibling.style.background = '#f0f9ff';
    });
});
</script>
</body>
</html>
