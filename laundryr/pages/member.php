<?php
require_once '../config/db.php';
isLogin();
$is_owner = $_SESSION['user_role'] === 'owner';

$msg = '';

if (!$is_owner && isset($_POST['tambah'])) {
    $nama    = $conn->real_escape_string($_POST['nama']);
    $telepon = $conn->real_escape_string($_POST['telepon']);
    $alamat  = $conn->real_escape_string($_POST['alamat']);
    $outlet  = (int)$_POST['outlet_id'];
    $conn->query("INSERT INTO member (nama, telepon, alamat, outlet_id) VALUES ('$nama','$telepon','$alamat',$outlet)");
    $msg = ['type'=>'success','text'=>'Member berhasil ditambahkan.'];
}

if (!$is_owner && isset($_POST['edit'])) {
    $id      = (int)$_POST['id'];
    $nama    = $conn->real_escape_string($_POST['nama']);
    $telepon = $conn->real_escape_string($_POST['telepon']);
    $alamat  = $conn->real_escape_string($_POST['alamat']);
    $outlet  = (int)$_POST['outlet_id'];
    $conn->query("UPDATE member SET nama='$nama', telepon='$telepon', alamat='$alamat', outlet_id=$outlet WHERE id=$id");
    $msg = ['type'=>'success','text'=>'Member berhasil diupdate.'];
}

if (!$is_owner && isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM member WHERE id=$id");
    $msg = ['type'=>'warning','text'=>'Member berhasil dihapus.'];
}

$members = $conn->query("SELECT m.*, o.nama_outlet FROM member m LEFT JOIN outlet o ON m.outlet_id=o.id ORDER BY m.id DESC");
$outlets = $conn->query("SELECT id, nama_outlet FROM outlet ORDER BY nama_outlet");
$outlet_list = [];
while ($o = $outlets->fetch_assoc()) $outlet_list[] = $o;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-person-badge me-2 text-warning"></i>Manajemen Member</p>
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
                <h5>Daftar Member</h5>
                <p>Data pelanggan member</p>
            </div>
            <?php if (!$is_owner): ?>
            <button class="btn btn-warning btn-sm px-3 text-dark" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i> Tambah Member
            </button>
            <?php endif; ?>
        </div>
        <div class="card card-table">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Outlet</th>
                            <th>Dibuat</th>
                            <th style="width:120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no=1; while($row=$members->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width:32px;height:32px;font-size:.75rem;background:#2563eb">
                                        <?= strtoupper(substr($row['nama'],0,1)) ?>
                                    </div>
                                    <?= htmlspecialchars($row['nama']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['telepon']) ?: '-' ?></td>
                            <td class="text-muted"><?= htmlspecialchars($row['alamat']) ?: '-' ?></td>
                            <td>
                                <?php if ($row['nama_outlet']): ?>
                                    <span class="badge" style="background:#dcfce7;color:#16a34a">
                                        <i class="bi bi-shop me-1"></i><?= htmlspecialchars($row['nama_outlet']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php if (!$is_owner): ?>
                                <button class="btn btn-sm btn-outline-warning me-1" onclick="editMember(<?= htmlspecialchars(json_encode($row)) ?>)" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus member ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-muted small">-</span>
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
                <h5 class="modal-title"><i class="bi bi-person-plus me-2 text-warning"></i>Tambah Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required></div>
                <div class="mb-3"><label class="form-label">Telepon</label><input type="text" name="telepon" class="form-control" placeholder="Nomor telepon"></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea></div>
                <div class="mb-1"><label class="form-label">Outlet</label>
                    <select name="outlet_id" class="form-select">
                        <option value="0">-- Pilih Outlet --</option>
                        <?php foreach ($outlet_list as $o): ?>
                            <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama_outlet']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah" class="btn btn-warning btn-sm px-4 text-dark">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="nama" id="edit_nama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Telepon</label><input type="text" name="telepon" id="edit_telepon" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="edit_alamat" class="form-control" rows="2"></textarea></div>
                <div class="mb-1"><label class="form-label">Outlet</label>
                    <select name="outlet_id" id="edit_outlet" class="form-select">
                        <option value="0">-- Pilih Outlet --</option>
                        <?php foreach ($outlet_list as $o): ?>
                            <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama_outlet']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="edit" class="btn btn-warning btn-sm px-4 text-dark">Update</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editMember(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama').value = data.nama;
    document.getElementById('edit_telepon').value = data.telepon;
    document.getElementById('edit_alamat').value = data.alamat;
    document.getElementById('edit_outlet').value = data.outlet_id;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
</body>
</html>
