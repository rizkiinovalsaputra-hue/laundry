<?php
include '../config/db.php';
isLogin();

$is_owner = $_SESSION['user_role'] == 'owner';
$can_edit = ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'kasir');

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add' && $can_edit) {
            $outlet_id = $_POST['outlet_id'];
            $jenis = $_POST['jenis'];
            $nama_paket = $_POST['nama_paket'];
            $harga = $_POST['harga'];
            
            $stmt = $conn->prepare("INSERT INTO tb_paket (outlet_id, jenis, nama_paket, harga) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $outlet_id, $jenis, $nama_paket, $harga);
            $stmt->execute();
        }
        
        if ($_POST['action'] == 'edit' && !$is_owner) {
            $id = $_POST['id'];
            $outlet_id = $_POST['outlet_id'];
            $jenis = $_POST['jenis'];
            $nama_paket = $_POST['nama_paket'];
            $harga = $_POST['harga'];
            
            $stmt = $conn->prepare("UPDATE tb_paket SET id_outlet=?, jenis=?, nama_paket=?, harga=? WHERE `int`=?");
            $stmt->bind_param("issii", $outlet_id, $jenis, $nama_paket, $harga, $id);
            $stmt->execute();
        }
        
        if ($_POST['action'] == 'delete' && !$is_owner) {
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM tb_paket WHERE `int`=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    }
}

// Get outlets for dropdown
$outlets = $conn->query("SELECT * FROM outlet ORDER BY nama_outlet");

// Get pakets with outlet info
$pakets = $conn->query("SELECT p.*, o.nama_outlet FROM tb_paket p LEFT JOIN outlet o ON p.id_outlet = o.id ORDER BY p.int DESC");

$page = 'paket.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Paket - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-box me-2 text-primary"></i>Kelola Paket</p>
        <span class="text-muted small"><?= date('l, d F Y') ?></span>
    </div>
    <div class="content-area">

        <!-- Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Daftar Paket Laundry</h5>
                <p class="text-muted small mb-0">Kelola paket layanan laundry untuk setiap outlet</p>
            </div>
            <?php if (!$is_owner): ?>
            <a href="tambah_paket.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Tambah Paket
            </a>
            <?php endif; ?>
        </div>

        <!-- Paket Table -->
        <div class="card card-table">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Outlet</th>
                                <th>Jenis</th>
                                <th>Nama Paket</th>
                                <th>Harga</th>
                                <th>Tanggal Dibuat</th>
                                <?php if (!$is_owner): ?>
                                <th class="text-center">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = $pakets->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                             style="width:30px;height:30px;font-size:.72rem;background:#16a34a">
                                            <i class="bi bi-shop"></i>
                                        </div>
                                        <?= htmlspecialchars($row['nama_outlet']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $row['jenis'] == 'kiloan' ? 'primary' : 
                                        ($row['jenis'] == 'selimut' ? 'success' : 
                                        ($row['jenis'] == 'bed_cover' ? 'info' : 
                                        ($row['jenis'] == 'kaos' ? 'warning' : 'secondary'))) 
                                    ?>">
                                        <?= ucfirst(str_replace('_', ' ', $row['jenis'])) ?>
                                    </span>
                                </td>
                                <td class="fw-medium"><?= htmlspecialchars($row['nama_paket']) ?></td>
                                <td class="fw-bold text-success">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td class="text-muted small"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                <?php if (!$is_owner): ?>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editPaket(<?= htmlspecialchars(json_encode($row)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deletePaket(<?= $row['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($pakets->num_rows == 0): ?>
                            <tr>
                                <td colspan="<?= $is_owner ? 6 : 7 ?>" class="text-center py-5 text-muted">
                                    <i class="bi bi-box display-1 d-block mb-3 opacity-25"></i>
                                    <p class="mb-0">Belum ada paket yang terdaftar</p>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Tambah Paket Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Outlet <span class="text-danger">*</span></label>
                        <select name="outlet_id" class="form-select" required>
                            <option value="">Pilih Outlet</option>
                            <?php $outlets->data_seek(0); while ($outlet = $outlets->fetch_assoc()): ?>
                            <option value="<?= $outlet['id'] ?>"><?= $outlet['nama_outlet'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Jenis Paket <span class="text-danger">*</span></label>
                        <select name="jenis" class="form-select" required>
                            <option value="">Pilih Jenis</option>
                            <option value="kiloan">Kiloan</option>
                            <option value="selimut">Selimut</option>
                            <option value="bed_cover">Bed Cover</option>
                            <option value="kaos">Kaos</option>
                            <option value="lain">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" name="nama_paket" class="form-control" placeholder="Contoh: Cuci Kering Kiloan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Harga <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="harga" class="form-control" placeholder="0" step="100" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Outlet <span class="text-danger">*</span></label>
                        <select name="outlet_id" id="edit_outlet_id" class="form-select" required>
                            <option value="">Pilih Outlet</option>
                            <?php $outlets->data_seek(0); while ($outlet = $outlets->fetch_assoc()): ?>
                            <option value="<?= $outlet['id'] ?>"><?= $outlet['nama_outlet'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Jenis Paket <span class="text-danger">*</span></label>
                        <select name="jenis" id="edit_jenis" class="form-select" required>
                            <option value="kiloan">Kiloan</option>
                            <option value="selimut">Selimut</option>
                            <option value="bed_cover">Bed Cover</option>
                            <option value="kaos">Kaos</option>
                            <option value="lain">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" name="nama_paket" id="edit_nama_paket" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Harga <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="harga" id="edit_harga" class="form-control" step="100" min="0" required>
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
    function editPaket(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_outlet_id').value = data.outlet_id;
        document.getElementById('edit_jenis').value = data.jenis;
        document.getElementById('edit_nama_paket').value = data.nama_paket;
        document.getElementById('edit_harga').value = data.harga;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function deletePaket(id) {
        if (confirm('Yakin ingin menghapus paket ini?\n\nData yang sudah dihapus tidak dapat dikembalikan.')) {
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