<?php
require_once '../config/db.php';
isLogin();
$is_owner = $_SESSION['user_role'] === 'owner';
$is_kasir = $_SESSION['user_role'] === 'kasir';

$msg = '';

// Hanya admin yang bisa tambah/edit/hapus outlet
if ($_SESSION['user_role'] === 'admin' && isset($_POST['tambah'])) {
    $nama    = $conn->real_escape_string($_POST['nama_outlet']);
    $alamat  = $conn->real_escape_string($_POST['alamat']);
    $telepon = $conn->real_escape_string($_POST['telepon']);
    $conn->query("INSERT INTO outlet (nama_outlet, alamat, telepon) VALUES ('$nama','$alamat','$telepon')");
    $msg = ['type'=>'success','text'=>'Outlet berhasil ditambahkan.'];
}

if ($_SESSION['user_role'] === 'admin' && isset($_POST['edit'])) {
    $id      = (int)$_POST['id'];
    $nama    = $conn->real_escape_string($_POST['nama_outlet']);
    $alamat  = $conn->real_escape_string($_POST['alamat']);
    $telepon = $conn->real_escape_string($_POST['telepon']);
    $conn->query("UPDATE outlet SET nama_outlet='$nama', alamat='$alamat', telepon='$telepon' WHERE id=$id");
    $msg = ['type'=>'success','text'=>'Outlet berhasil diupdate.'];
}

if ($_SESSION['user_role'] === 'admin' && isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM outlet WHERE id=$id");
    $msg = ['type'=>'warning','text'=>'Outlet berhasil dihapus.'];
}

$outlets = $conn->query("SELECT * FROM outlet ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Outlet - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-shop me-2 text-success"></i>Manajemen Outlet</p>
    </div>
    <div class="content-area">
        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg['type'] ?> d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-<?= $msg['type']==='success'?'check-circle':'exclamation-triangle' ?>-fill"></i>
                <?= $msg['text'] ?>
            </div>
        <?php endif; ?>
        <div class="page-header">
            <div>
                <h5>Daftar Outlet</h5>
                <p>Data outlet laundry <?= $is_kasir ? '(View Only)' : '' ?></p>
            </div>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <button class="btn btn-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i> Tambah Outlet
            </button>
            <?php endif; ?>
        </div>
        <div class="card card-table">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Nama Outlet</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Dibuat</th>
                            <th style="width:120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no=1; while($row=$outlets->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center"
                                         style="width:32px;height:32px;background:#dcfce7">
                                        <i class="bi bi-shop text-success"></i>
                                    </div>
                                    <span class="fw-500"><?= htmlspecialchars($row['nama_outlet']) ?></span>
                                </div>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($row['alamat']) ?: '-' ?></td>
                            <td><?= htmlspecialchars($row['telepon']) ?: '-' ?></td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <button class="btn btn-sm btn-outline-warning me-1" onclick="editOutlet(<?= htmlspecialchars(json_encode($row)) ?>)" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus outlet ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-muted small">View Only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-shop me-2 text-success"></i>Tambah Outlet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama Outlet</label><input type="text" name="nama_outlet" class="form-control" placeholder="Nama outlet" required></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea></div>
                <div class="mb-1"><label class="form-label">Telepon</label><input type="text" name="telepon" class="form-control" placeholder="Nomor telepon"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah" class="btn btn-success btn-sm px-4">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Outlet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3"><label class="form-label">Nama Outlet</label><input type="text" name="nama_outlet" id="edit_nama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="edit_alamat" class="form-control" rows="2"></textarea></div>
                <div class="mb-1"><label class="form-label">Telepon</label><input type="text" name="telepon" id="edit_telepon" class="form-control"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="edit" class="btn btn-warning btn-sm px-4">Update</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editOutlet(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama').value = data.nama_outlet;
    document.getElementById('edit_alamat').value = data.alamat;
    document.getElementById('edit_telepon').value = data.telepon;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
</body>
</html>
