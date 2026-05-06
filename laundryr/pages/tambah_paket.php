<?php
include '../config/db.php';
isLogin();

$success = false;
$error   = '';

if ($_POST) {
    try {
        $id_outlet  = (int)$_POST['id_outlet'];
        $jenis      = $conn->real_escape_string($_POST['jenis']);
        $nama_paket = $conn->real_escape_string($_POST['nama_paket']);
        $harga      = (int)$_POST['harga'];

        if (!$id_outlet || !$jenis || !$nama_paket || !$harga) {
            throw new Exception('Semua field harus diisi');
        }

        $stmt = $conn->prepare("INSERT INTO tb_paket (id_outlet, jenis, nama_paket, harga) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Prepare statement gagal: ' . $conn->error);
        }
        
        $stmt->bind_param("issi", $id_outlet, $jenis, $nama_paket, $harga);
        if (!$stmt->execute()) {
            throw new Exception('Execute gagal: ' . $stmt->error);
        }
        
        $success = true;
        header("refresh:2;url=paket.php");
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$outlets = $conn->query("SELECT * FROM outlet ORDER BY nama_outlet");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Paket - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
</head>
<body>
<?php require_once '../config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Paket</p>
        <span class="text-muted small"><?= date('l, d F Y') ?></span>
    </div>
    <div class="content-area">
        <div class="mb-3">
            <a href="paket.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4"><i class="bi bi-box me-2 text-primary"></i>Informasi Paket Baru</h5>

                        <?php if ($success): ?>
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div>Paket berhasil disimpan! Mengalihkan ke halaman paket...</div>
                        </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Outlet <span class="text-danger">*</span></label>
                                <select name="id_outlet" class="form-select" required>
                                    <option value="">Pilih Outlet</option>
                                    <?php while ($o = $outlets->fetch_assoc()): ?>
                                    <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama_outlet']) ?></option>
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
                            <div class="mb-4">
                                <label class="form-label fw-medium">Harga <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="harga" class="form-control" placeholder="0" min="0" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Simpan Paket</button>
                                <a href="paket.php" class="btn btn-outline-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
