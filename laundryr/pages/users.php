<?php
require_once '../config/db.php';
isLogin();

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /laundryr/dashboard.php"); exit;
}

$msg = '';

if (isset($_POST['tambah'])) {
    $nama     = $conn->real_escape_string($_POST['nama']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']);
    $role     = $_POST['role'];
    $conn->query("INSERT INTO users (nama, username, password, role) VALUES ('$nama','$username','$password','$role')");
    $msg = ['type'=>'success','text'=>'User berhasil ditambahkan.'];
}

if (isset($_POST['edit'])) {
    $id       = (int)$_POST['id'];
    $nama     = $conn->real_escape_string($_POST['nama']);
    $username = $conn->real_escape_string($_POST['username']);
    $role     = $_POST['role'];
    $sql = "UPDATE users SET nama='$nama', username='$username', role='$role'";
    if (!empty($_POST['password'])) $sql .= ", password='".md5($_POST['password'])."'";
    $conn->query("$sql WHERE id=$id");
    $msg = ['type'=>'success','text'=>'User berhasil diupdate.'];
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM users WHERE id=$id");
    $msg = ['type'=>'warning','text'=>'User berhasil dihapus.'];
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-people me-2 text-primary"></i>Manajemen Users</p>
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
                <h5>Daftar Users</h5>
                <p>Kelola akun pengguna sistem</p>
            </div>
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-1"></i> Tambah User
            </button>
        </div>
        <div class="card card-table">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Dibuat</th>
                            <th style="width:120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no=1; while($row=$users->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width:32px;height:32px;font-size:.75rem;background:<?= $row['role']==='admin'?'#2563eb':'#64748b' ?>">
                                        <?= strtoupper(substr($row['nama'],0,1)) ?>
                                    </div>
                                    <?= htmlspecialchars($row['nama']) ?>
                                </div>
                            </td>
                            <td><code class="text-primary"><?= htmlspecialchars($row['username']) ?></code></td>
                            <td>
                                <span class="badge" style="background:<?= $row['role']==='admin'?'#ede9fe;color:#7c3aed':'#f1f5f9;color:#64748b' ?>">
                                    <?= ucfirst($row['role']) ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning me-1" onclick="editUser(<?= htmlspecialchars(json_encode($row)) ?>)" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus user ini?')" title="Hapus">
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required></div>
                <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" placeholder="Username login" required></div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                <div class="mb-1"><label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="kasir">Kasir</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah" class="btn btn-primary btn-sm px-4">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="nama" id="edit_nama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" id="edit_username" class="form-control" required></div>
                <div class="mb-3">
                    <label class="form-label">Password Baru <span class="text-muted fw-normal">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Password baru">
                </div>
                <div class="mb-1"><label class="form-label">Role</label>
                    <select name="role" id="edit_role" class="form-select">
                        <option value="kasir">Kasir</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
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
function editUser(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama').value = data.nama;
    document.getElementById('edit_username').value = data.username;
    document.getElementById('edit_role').value = data.role;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
</body>
</html>
