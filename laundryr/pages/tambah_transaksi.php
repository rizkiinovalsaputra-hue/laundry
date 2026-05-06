<?php
include '../config/db.php';
isLogin();

$error = '';
$success = false;

// Debug session
if (!isset($_SESSION['user_id'])) {
    $error = 'Session user_id tidak ditemukan. Silakan login ulang.';
}

if ($_POST && isset($_POST['action']) && $_POST['action'] == 'add' && !$error) {
    try {
        $id_outlet      = (int)$_POST['id_outlet'];
        $kode_invoice   = $conn->real_escape_string($_POST['kode_invoice']);
        $id_member      = (int)($_POST['id_member'] ?: 0);
        $tgl            = $_POST['tgl'] . ' 00:00:00';
        $batas_waktu    = str_replace('T', ' ', $_POST['batas_waktu']);
        $biaya_tambahan = (int)($_POST['biaya_tambahan'] ?: 0);
        $diskon         = (float)($_POST['diskon'] ?: 0);
        $pajak          = (int)($_POST['pajak'] ?: 0);
        $status         = $_POST['status'];
        $dibayar        = $_POST['dibayar'];
        $tgl_bayar      = date('Y-m-d H:i:s'); // Selalu isi dengan datetime sekarang
        $id_user        = (int)$_SESSION['user_id'];

        // Validasi input
        if (!$id_outlet || !$kode_invoice || !$id_user) {
            throw new Exception('Data tidak lengkap: outlet, invoice, atau user ID kosong');
        }

        $stmt = $conn->prepare("INSERT INTO tb_transaksi (id_outlet, kode_invoice, id_member, tgl, batas_waktu, tgl_bayar, biaya_tambahan, diskon, pajak, status, dibayar, id_user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Prepare statement gagal: ' . $conn->error);
        }
        
        $stmt->bind_param("isiissdisssi", $id_outlet, $kode_invoice, $id_member, $tgl, $batas_waktu, $tgl_bayar, $biaya_tambahan, $diskon, $pajak, $status, $dibayar, $id_user);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute gagal: ' . $stmt->error);
        }
        
        $transaksi_id = $conn->insert_id;
        if (!$transaksi_id) {
            throw new Exception('Gagal mendapatkan ID transaksi');
        }

        // Insert detail items
        if (!empty($_POST['id_paket'])) {
            foreach ($_POST['id_paket'] as $i => $id_paket) {
                if (!$id_paket) continue;
                $qty        = (float)$_POST['qty'][$i];
                $keterangan = trim($_POST['keterangan'][$i] ?? '');
                if (empty($keterangan)) $keterangan = '-';
                
                $stmt2 = $conn->prepare("INSERT INTO tb_detail_transaksi (id_transaksi, id_paket, qty, keterangan) VALUES (?, ?, ?, ?)");
                if (!$stmt2) {
                    throw new Exception('Prepare detail gagal: ' . $conn->error);
                }
                
                $stmt2->bind_param("iids", $transaksi_id, $id_paket, $qty, $keterangan);
                if (!$stmt2->execute()) {
                    throw new Exception('Execute detail gagal: ' . $stmt2->error);
                }
            }
        }

        $success = true;
        // Redirect setelah 2 detik
        header("refresh:2;url=transaksi.php");
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$outlets = $conn->query("SELECT * FROM outlet ORDER BY nama_outlet");
$members = $conn->query("SELECT * FROM member ORDER BY nama");

// Build pakets per outlet as JSON
$pakets_by_outlet = [];
$pakets_raw = $conn->query("SELECT p.*, o.nama_outlet FROM tb_paket p JOIN outlet o ON p.id_outlet = o.id ORDER BY p.nama_paket");
if ($pakets_raw) {
    while ($r = $pakets_raw->fetch_assoc()) {
        $pakets_by_outlet[$r['id_outlet']][] = $r;
    }
}
$pakets_json = json_encode($pakets_by_outlet);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Transaksi - LaundryR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/laundryr/assets/style.css" rel="stylesheet">
    <style>
        .item-row { background:#f8fafc; border-radius:10px; padding:1rem; margin-bottom:.75rem; border:1px solid #e2e8f0; }
        .summary-box { background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; padding:1.25rem; }
        .summary-row { display:flex; justify-content:space-between; padding:.35rem 0; font-size:.9rem; }
        .summary-row.total { border-top:2px solid #e2e8f0; margin-top:.5rem; padding-top:.75rem; font-weight:700; font-size:1rem; }
        .section-title { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; margin-bottom:.75rem; }
    </style>
</head>
<body>
<?php require_once '../config/navbar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <p class="page-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Transaksi</p>
        <span class="text-muted small"><?= date('l, d F Y') ?></span>
    </div>
    <div class="content-area">

        <div class="mb-3">
            <a href="transaksi.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>Transaksi berhasil disimpan! Mengalihkan ke halaman transaksi...</div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="formTransaksi">
            <input type="hidden" name="action" value="add">
            <div class="row g-4">

                <!-- LEFT -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <p class="section-title"><i class="bi bi-info-circle me-1"></i>Informasi Transaksi</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Outlet <span class="text-danger">*</span></label>
                                    <select name="id_outlet" id="id_outlet" class="form-select" required onchange="loadPakets(this.value)">
                                        <option value="">Pilih Outlet</option>
                                        <?php while ($o = $outlets->fetch_assoc()): ?>
                                        <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama_outlet']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Kode Invoice <span class="text-danger">*</span></label>
                                    <input type="text" name="kode_invoice" class="form-control" value="LDR<?= date('ymd') . rand(100,999) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Member</label>
                                    <select name="id_member" class="form-select">
                                        <option value="">Non-Member</option>
                                        <?php while ($m = $members->fetch_assoc()): ?>
                                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tgl" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Batas Waktu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="batas_waktu" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime('+2 days')) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="baru">Baru</option>
                                        <option value="proses">Proses</option>
                                        <option value="selesai">Selesai</option>
                                        <option value="diambil">Diambil</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Pembayaran</label>
                                    <select name="dibayar" class="form-select">
                                        <option value="belum_dibayar">Belum Bayar</option>
                                        <option value="bayar">Sudah Bayar</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Item Paket -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="section-title mb-0"><i class="bi bi-box me-1"></i>Item Paket</p>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addItem()" id="btnAddItem" disabled>
                                    <i class="bi bi-plus-lg me-1"></i>Tambah Item
                                </button>
                            </div>
                            <div id="itemsContainer">
                                <div class="text-center py-4 text-muted" id="emptyMsg">
                                    <i class="bi bi-box display-6 d-block mb-2 opacity-25"></i>
                                    <small>Pilih outlet terlebih dahulu</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Ringkasan -->
                <div class="col-lg-4">
                    <div class="card" style="position:sticky;top:1rem">
                        <div class="card-body">
                            <p class="section-title"><i class="bi bi-calculator me-1"></i>Ringkasan Biaya</p>
                            <div class="summary-box mb-3">
                                <div class="summary-row"><span class="text-muted">Subtotal</span><span id="summarySubtotal">Rp 0</span></div>
                                <div class="summary-row"><span class="text-muted">Biaya Tambahan</span><span id="summaryBiaya">Rp 0</span></div>
                                <div class="summary-row"><span class="text-muted">Pajak</span><span id="summaryPajak">Rp 0</span></div>
                                <div class="summary-row text-danger"><span>Diskon</span><span id="summaryDiskon">- Rp 0</span></div>
                                <div class="summary-row total"><span>Total</span><span id="summaryTotal" class="text-success">Rp 0</span></div>
                            </div>

                            <p class="section-title"><i class="bi bi-sliders me-1"></i>Biaya Lainnya</p>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Biaya Tambahan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="biaya_tambahan" id="biaya_tambahan" class="form-control" value="0" min="0" oninput="updateSummary()">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Diskon</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="diskon" id="diskon" class="form-control" value="0" min="0" oninput="updateSummary()">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Pajak</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="pajak" id="pajak" class="form-control" value="0" min="0" oninput="updateSummary()">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg me-2"></i>Simpan Transaksi
                            </button>
                            <a href="transaksi.php" class="btn btn-outline-secondary w-100 mt-2">Batal</a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const paketsByOutlet = <?= $pakets_json ?>;
let itemCount = 0;

function loadPakets(outletId) {
    const btn = document.getElementById('btnAddItem');
    const container = document.getElementById('itemsContainer');
    container.querySelectorAll('.item-row').forEach(el => el.remove());
    itemCount = 0;
    updateSummary();

    if (!outletId || !paketsByOutlet[outletId]) {
        btn.disabled = true;
        if (!document.getElementById('emptyMsg')) {
            container.innerHTML = `<div class="text-center py-4 text-muted" id="emptyMsg">
                <i class="bi bi-box display-6 d-block mb-2 opacity-25"></i>
                <small>Pilih outlet terlebih dahulu</small></div>`;
        }
        return;
    }
    btn.disabled = false;
    const empty = document.getElementById('emptyMsg');
    if (empty) empty.remove();
    addItem();
}

function addItem() {
    const outletId = document.getElementById('id_outlet').value;
    const pakets = paketsByOutlet[outletId] || [];
    const empty = document.getElementById('emptyMsg');
    if (empty) empty.remove();

    const idx = itemCount++;
    const options = pakets.map(p =>
        `<option value="${p.int}" data-harga="${p.harga}">${p.nama_paket} (${ucfirst(p.jenis)}) - Rp ${formatRp(p.harga)}</option>`
    ).join('');

    const div = document.createElement('div');
    div.className = 'item-row';
    div.id = `item_${idx}`;
    div.innerHTML = `
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-medium" style="font-size:.82rem">Paket <span class="text-danger">*</span></label>
                <select name="id_paket[]" class="form-select form-select-sm" required onchange="updateSummary()">
                    <option value="">Pilih Paket</option>${options}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-medium" style="font-size:.82rem">Qty <span class="text-danger">*</span></label>
                <input type="number" name="qty[]" id="qty_${idx}" class="form-control form-control-sm" step="0.1" min="0.1" value="1" required oninput="updateSummary()">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium" style="font-size:.82rem">Keterangan</label>
                <input type="text" name="keterangan[]" class="form-control form-control-sm" placeholder="Opsional">
            </div>
            <div class="col-md-2 d-flex align-items-end justify-content-between">
                <span class="fw-bold text-success small" id="sub_${idx}">Rp 0</span>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${idx})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>`;
    document.getElementById('itemsContainer').appendChild(div);
    updateSummary();
}

function removeItem(idx) {
    const el = document.getElementById(`item_${idx}`);
    if (el) el.remove();
    const container = document.getElementById('itemsContainer');
    if (!container.querySelector('.item-row')) {
        container.innerHTML = `<div class="text-center py-4 text-muted" id="emptyMsg">
            <i class="bi bi-box display-6 d-block mb-2 opacity-25"></i>
            <small>Klik "Tambah Item" untuk menambahkan paket</small></div>`;
    }
    updateSummary();
}

function updateSummary() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const select = row.querySelector('select[name="id_paket[]"]');
        const qtyInput = row.querySelector('input[name="qty[]"]');
        const subEl = row.querySelector('[id^="sub_"]');
        if (!select || !qtyInput) return;
        const harga = parseFloat(select.options[select.selectedIndex]?.dataset?.harga || 0);
        const qty   = parseFloat(qtyInput.value || 0);
        const sub   = harga * qty;
        subtotal += sub;
        if (subEl) subEl.textContent = 'Rp ' + formatRp(sub);
    });

    const biaya = parseFloat(document.getElementById('biaya_tambahan').value || 0);
    const diskon = parseFloat(document.getElementById('diskon').value || 0);
    const pajak  = parseFloat(document.getElementById('pajak').value || 0);
    const total  = subtotal + biaya + pajak - diskon;

    document.getElementById('summarySubtotal').textContent = 'Rp ' + formatRp(subtotal);
    document.getElementById('summaryBiaya').textContent    = 'Rp ' + formatRp(biaya);
    document.getElementById('summaryPajak').textContent    = 'Rp ' + formatRp(pajak);
    document.getElementById('summaryDiskon').textContent   = '- Rp ' + formatRp(diskon);
    document.getElementById('summaryTotal').textContent    = 'Rp ' + formatRp(total);
}

function formatRp(n) { return Math.round(n).toLocaleString('id-ID'); }
function ucfirst(str) { return str.charAt(0).toUpperCase() + str.slice(1).replace('_', ' '); }
</script>
</body>
</html>
