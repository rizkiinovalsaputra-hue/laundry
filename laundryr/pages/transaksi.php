<?php
include '../config/db.php';
isLogin();

$is_owner = $_SESSION['user_role'] == 'owner';
$can_edit = ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'kasir');

if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] == 'add' && !$is_owner) {
        $outlet_id    = $_POST['outlet_id'];
        $kode_invoice = $_POST['kode_invoice'];
        $member_id    = $_POST['member_id'] ?: null;
        $tgl          = $_POST['tgl'];
        $batas_waktu  = $_POST['batas_waktu'];
        $biaya_tambahan = $_POST['biaya_tambahan'] ?: 0;
        $diskon       = $_POST['diskon'] ?: 0;
        $pajak        = $_POST['pajak'] ?: 0;
        $status       = $_POST['status'];
        $dibayar      = $_POST['dibayar'];
        $user_id      = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO tb_transaksi (outlet_id, kode_invoice, member_id, tgl, batas_waktu, biaya_tambahan, diskon, pajak, status, dibayar, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isisdddsssi", $outlet_id, $kode_invoice, $member_id, $tgl, $batas_waktu, $biaya_tambahan, $diskon, $pajak, $status, $dibayar, $user_id);
        $stmt->execute();
    }

    if ($_POST['action'] == 'edit' && $can_edit) {
        $id           = $_POST['id'];
        $outlet_id    = $_POST['outlet_id'];
        $member_id    = $_POST['member_id'] ?: null;
        $tgl          = $_POST['tgl'];
        $batas_waktu  = $_POST['batas_waktu'];
        $biaya_tambahan = $_POST['biaya_tambahan'] ?: 0;
        $diskon       = $_POST['diskon'] ?: 0;
        $pajak        = $_POST['pajak'] ?: 0;
        $status       = $_POST['status'];
        $dibayar      = $_POST['dibayar'];
        $tgl_bayar    = ($dibayar == 'bayar') ? date('Y-m-d H:i:s') : null;

        $stmt = $conn->prepare("UPDATE tb_transaksi SET id_outlet=?, id_member=?, tgl=?, batas_waktu=?, tgl_bayar=?, biaya_tambahan=?, diskon=?, pajak=?, status=?, dibayar=? WHERE id=?");
        $stmt->bind_param("iisssddsssi", $outlet_id, $member_id, $tgl, $batas_waktu, $tgl_bayar, $biaya_tambahan, $diskon, $pajak, $status, $dibayar, $id);
        $stmt->execute();
    }

    if ($_POST['action'] == 'delete' && $can_edit) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM tb_transaksi WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

$outlets  = $conn->query("SELECT * FROM outlet ORDER BY nama_outlet");
$members  = $conn->query("SELECT * FROM member ORDER BY nama");
$transaksi = $conn->query("
    SELECT t.*, o.nama_outlet, m.nama as nama_member
    FROM tb_transaksi t
    LEFT JOIN outlet o ON t.id_outlet = o.id
    LEFT JOIN member m ON t.id_member = m.id
    ORDER BY t.id DESC
");

// Stats
$total_transaksi = $conn->query("SELECT COUNT(*) as c FROM tb_transaksi")->fetch_assoc()['c'];
$total_lunas     = $conn->query("SELECT COUNT(*) as c FROM tb_transaksi WHERE dibayar='dibayar'")->fetch_assoc()['c'];
$total_proses    = $conn->query("SELECT COUNT(*) as c FROM tb_transaksi WHERE status='proses'")->fetch_assoc()['c'];
$total_baru      = $conn->query("SELECT COUNT(*) as c FROM tb_transaksi WHERE status='baru'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-receipt me-2 text-primary"></i>Transaksi</p>
        <span class="text-muted small"><?= date('l, d F Y') ?></span>
    </div>
    <div class="content-area">

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#dbeafe">
                            <i class="bi bi-receipt" style="color:#2563eb"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold text-dark lh-1"><?= $total_transaksi ?></div>
                            <div class="text-muted small mt-1">Total Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#fef3c7">
                            <i class="bi bi-hourglass-split" style="color:#d97706"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold text-dark lh-1"><?= $total_baru ?></div>
                            <div class="text-muted small mt-1">Transaksi Baru</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#fce7f3">
                            <i class="bi bi-arrow-repeat" style="color:#db2777"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold text-dark lh-1"><?= $total_proses ?></div>
                            <div class="text-muted small mt-1">Sedang Proses</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-card bg-white">
                    <div class="card-body d-flex align-items-center gap-3 p-3">
                        <div class="icon-box" style="background:#dcfce7">
                            <i class="bi bi-check-circle" style="color:#16a34a"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold text-dark lh-1"><?= $total_lunas ?></div>
                            <div class="text-muted small mt-1">Sudah Lunas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">Daftar Transaksi</h5>
                <p class="text-muted small mb-0">Kelola semua transaksi laundry</p>
            </div>
            <?php if ($can_edit): ?>
            <a href="tambah_transaksi.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Tambah Transaksi
            </a>
            <?php endif; ?>
        </div>

        <!-- Table -->
        <div class="card card-table">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Invoice</th>
                                <th>Member</th>
                                <th>Outlet</th>
                                <th>Tanggal</th>
                                <th>Batas Waktu</th>
                                <th>Status</th>
                                <th>Pembayaran</th>
                                <th>Total</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; while ($row = $transaksi->fetch_assoc()):
                            $q = $conn->query("SELECT SUM(p.harga * d.qty) as sub FROM tb_detail_transaksi d JOIN tb_paket p ON d.id_paket=p.`int` WHERE d.id_transaksi={$row['id']}");
                            $sub = $q->fetch_assoc()['sub'] ?? 0;
                            $total = $sub + $row['biaya_tambahan'] + $row['pajak'] - $row['diskon'];
                        ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= $no++ ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($row['kode_invoice']) ?></td>
                            <td>
                                <?php if ($row['nama_member']): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width:28px;height:28px;font-size:.7rem;background:#2563eb;flex-shrink:0">
                                        <?= strtoupper(substr($row['nama_member'], 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($row['nama_member']) ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted fst-italic">Non-Member</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($row['nama_outlet']) ?></td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($row['tgl'])) ?></td>
                            <td class="text-muted small"><?= date('d M Y H:i', strtotime($row['batas_waktu'])) ?></td>
                            <td>
                                <span class="badge bg-<?=
                                    $row['status'] == 'baru'    ? 'primary' :
                                    ($row['status'] == 'proses'  ? 'warning'  :
                                    ($row['status'] == 'selesai' ? 'success'  : 'info'))
                                ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $row['dibayar'] == 'dibayar' ? 'success' : 'danger' ?>">
                                    <?= $row['dibayar'] == 'dibayar' ? 'Lunas' : 'Belum Bayar' ?>
                                </span>
                            </td>
                            <td class="fw-bold text-success">Rp <?= number_format($total, 0, ',', '.') ?></td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="detail_transaksi.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($can_edit): ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editTransaksi(<?= htmlspecialchars(json_encode($row)) ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTransaksi(<?= $row['id'] ?>)" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($transaksi->num_rows == 0): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt display-1 d-block mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada transaksi</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Outlet <span class="text-danger">*</span></label>
                            <select name="outlet_id" id="edit_outlet_id" class="form-select" required>
                                <?php $outlets->data_seek(0); while ($o = $outlets->fetch_assoc()): ?>
                                <option value="<?= $o['id'] ?>"><?= $o['nama_outlet'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Member</label>
                            <select name="member_id" id="edit_member_id" class="form-select">
                                <option value="">Non-Member</option>
                                <?php $members->data_seek(0); while ($m = $members->fetch_assoc()): ?>
                                <option value="<?= $m['id'] ?>"><?= $m['nama'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tgl" id="edit_tgl" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Batas Waktu <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="batas_waktu" id="edit_batas_waktu" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="baru">Baru</option>
                                <option value="proses">Proses</option>
                                <option value="selesai">Selesai</option>
                                <option value="diambil">Diambil</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Pembayaran</label>
                            <select name="dibayar" id="edit_dibayar" class="form-select">
                                <option value="belum_bayar">Belum Bayar</option>
                                <option value="dibayar">Sudah Bayar</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Biaya Tambahan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="biaya_tambahan" id="edit_biaya_tambahan" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Diskon</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="diskon" id="edit_diskon" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Pajak</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="pajak" id="edit_pajak" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editTransaksi(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_outlet_id').value = data.outlet_id;
        document.getElementById('edit_member_id').value = data.member_id || '';
        document.getElementById('edit_tgl').value = data.tgl;
        document.getElementById('edit_batas_waktu').value = data.batas_waktu.replace(' ', 'T');
        document.getElementById('edit_biaya_tambahan').value = data.biaya_tambahan;
        document.getElementById('edit_diskon').value = data.diskon;
        document.getElementById('edit_pajak').value = data.pajak;
        document.getElementById('edit_status').value = data.status;
        document.getElementById('edit_dibayar').value = data.dibayar;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function deleteTransaksi(id) {
        if (confirm('Yakin ingin menghapus transaksi ini?\n\nData yang sudah dihapus tidak dapat dikembalikan.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
</body>
</html>
