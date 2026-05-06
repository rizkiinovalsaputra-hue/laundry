<?php
include '../config/db.php';
isLogin();

$is_owner = $_SESSION['role'] == 'owner';
$transaksi_id = $_GET['id'] ?? 0;

// Get transaction info
$transaksi_query = $conn->prepare("
    SELECT t.*, o.nama_outlet, m.nama as nama_member 
    FROM tb_transaksi t 
    LEFT JOIN outlet o ON t.outlet_id = o.id 
    LEFT JOIN member m ON t.member_id = m.id 
    WHERE t.id = ?
");
$transaksi_query->bind_param("i", $transaksi_id);
$transaksi_query->execute();
$transaksi = $transaksi_query->get_result()->fetch_assoc();

if (!$transaksi) {
    header("Location: transaksi.php");
    exit;
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add' && !$is_owner) {
            $paket_id = $_POST['paket_id'];
            $qty = $_POST['qty'];
            $keterangan = $_POST['keterangan'];
            
            $stmt = $conn->prepare("INSERT INTO tb_detail_transaksi (transaksi_id, paket_id, qty, keterangan) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iids", $transaksi_id, $paket_id, $qty, $keterangan);
            $stmt->execute();
        }
        
        if ($_POST['action'] == 'edit' && !$is_owner) {
            $id = $_POST['id'];
            $paket_id = $_POST['paket_id'];
            $qty = $_POST['qty'];
            $keterangan = $_POST['keterangan'];
            
            $stmt = $conn->prepare("UPDATE tb_detail_transaksi SET paket_id=?, qty=?, keterangan=? WHERE id=?");
            $stmt->bind_param("idsi", $paket_id, $qty, $keterangan, $id);
            $stmt->execute();
        }
        
        if ($_POST['action'] == 'delete' && !$is_owner) {
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM tb_detail_transaksi WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
}

// Get packages for dropdown (from same outlet)
$pakets = $conn->prepare("SELECT * FROM tb_paket WHERE outlet_id = ? ORDER BY nama_paket");
$pakets->bind_param("i", $transaksi['outlet_id']);
$pakets->execute();
$pakets_result = $pakets->get_result();

// Get detail transactions
$details = $conn->prepare("
    SELECT d.*, p.nama_paket, p.harga, p.jenis, (p.harga * d.qty) as subtotal
    FROM tb_detail_transaksi d 
    JOIN tb_paket p ON d.paket_id = p.id 
    WHERE d.transaksi_id = ? 
    ORDER BY d.id DESC
");
$details->bind_param("i", $transaksi_id);
$details->execute();
$details_result = $details->get_result();

// Calculate totals
$subtotal = 0;
$details_array = [];
while ($row = $details_result->fetch_assoc()) {
    $subtotal += $row['subtotal'];
    $details_array[] = $row;
}
$total = $subtotal + $transaksi['biaya_tambahan'] + $transaksi['pajak'] - $transaksi['diskon'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Transaksi <?= $transaksi['kode_invoice'] ?> - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h4>LaundryR</h4>
            <small><?= $_SESSION['nama'] ?> (<?= ucfirst($_SESSION['role']) ?>)</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php">Dashboard</a></li>
            <?php if ($_SESSION['role'] != 'kasir'): ?>
            <li><a href="users.php">Users</a></li>
            <li><a href="outlet.php">Outlet</a></li>
            <?php endif; ?>
            <li><a href="member.php">Member</a></li>
            <li><a href="paket.php">Paket</a></li>
            <li><a href="transaksi.php" class="active">Transaksi</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Detail Transaksi</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="transaksi.php">Transaksi</a></li>
                            <li class="breadcrumb-item active"><?= $transaksi['kode_invoice'] ?></li>
                        </ol>
                    </nav>
                </div>
                <?php if (!$is_owner): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Item</button>
                <?php endif; ?>
            </div>

            <!-- Transaction Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Informasi Transaksi</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Invoice:</strong></td>
                                    <td><?= $transaksi['kode_invoice'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Outlet:</strong></td>
                                    <td><?= $transaksi['nama_outlet'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Member:</strong></td>
                                    <td><?= $transaksi['nama_member'] ?: 'Non-Member' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal:</strong></td>
                                    <td><?= date('d/m/Y', strtotime($transaksi['tgl'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Batas Waktu:</strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($transaksi['batas_waktu'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $transaksi['status'] == 'baru' ? 'primary' : 
                                            ($transaksi['status'] == 'proses' ? 'warning' : 
                                            ($transaksi['status'] == 'selesai' ? 'success' : 'info')) 
                                        ?>">
                                            <?= ucfirst($transaksi['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Ringkasan Biaya</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td>Subtotal:</td>
                                    <td class="text-end">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Biaya Tambahan:</td>
                                    <td class="text-end">Rp <?= number_format($transaksi['biaya_tambahan'], 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Pajak:</td>
                                    <td class="text-end">Rp <?= number_format($transaksi['pajak'], 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Diskon:</td>
                                    <td class="text-end">-Rp <?= number_format($transaksi['diskon'], 0, ',', '.') ?></td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Total:</strong></td>
                                    <td class="text-end"><strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Status Bayar:</strong></td>
                                    <td class="text-end">
                                        <span class="badge bg-<?= $transaksi['dibayar'] == 'dibayar' ? 'success' : 'danger' ?>">
                                            <?= $transaksi['dibayar'] == 'dibayar' ? 'Lunas' : 'Belum Bayar' ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Items -->
            <div class="card">
                <div class="card-header">
                    <h5>Item Transaksi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Paket</th>
                                <th>Jenis</th>
                                <th>Harga Satuan</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th>Keterangan</th>
                                <?php if (!$is_owner): ?>
                                <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details_array as $row): ?>
                            <tr>
                                <td><?= $row['nama_paket'] ?></td>
                                <td><?= ucfirst($row['jenis']) ?></td>
                                <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td><?= $row['qty'] ?></td>
                                <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                                <td><?= $row['keterangan'] ?></td>
                                <?php if (!$is_owner): ?>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editDetail(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteDetail(<?= $row['id'] ?>)">Hapus</button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($details_array)): ?>
                            <tr>
                                <td colspan="<?= $is_owner ? 6 : 7 ?>" class="text-center">Belum ada item</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Paket</label>
                            <select name="paket_id" class="form-select" required>
                                <option value="">Pilih Paket</option>
                                <?php $pakets_result->data_seek(0); while ($paket = $pakets_result->fetch_assoc()): ?>
                                <option value="<?= $paket['id'] ?>" data-harga="<?= $paket['harga'] ?>">
                                    <?= $paket['nama_paket'] ?> - Rp <?= number_format($paket['harga'], 0, ',', '.') ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="qty" class="form-control" step="0.1" min="0.1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Paket</label>
                            <select name="paket_id" id="edit_paket_id" class="form-select" required>
                                <?php $pakets_result->data_seek(0); while ($paket = $pakets_result->fetch_assoc()): ?>
                                <option value="<?= $paket['id'] ?>">
                                    <?= $paket['nama_paket'] ?> - Rp <?= number_format($paket['harga'], 0, ',', '.') ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="qty" id="edit_qty" class="form-control" step="0.1" min="0.1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editDetail(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_paket_id').value = data.paket_id;
            document.getElementById('edit_qty').value = data.qty;
            document.getElementById('edit_keterangan').value = data.keterangan;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteDetail(id) {
            if (confirm('Yakin ingin menghapus item ini?')) {
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