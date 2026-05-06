<?php
$page = basename($_SERVER['PHP_SELF']);
$is_pages = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$base = $is_pages ? '/laundryr' : '/laundryr';
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <h5> LaundryR</h5>
        <small>Sistem Manajemen Laundry</small>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Menu</div>
        <?php if ($_SESSION['user_role'] === 'owner'): ?>
        <a href="<?= $base ?>/pages/owner_dashboard.php" class="nav-link <?= $page==='owner_dashboard.php'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="<?= $base ?>/pages/users.php" class="nav-link <?= $page==='users.php'?'active':'' ?>">
            <i class="bi bi-people"></i> Users
        </a>
        <a href="<?= $base ?>/pages/outlet.php" class="nav-link <?= $page==='outlet.php'?'active':'' ?>">
            <i class="bi bi-shop"></i> Outlet
        </a>
        <a href="<?= $base ?>/pages/member.php" class="nav-link <?= $page==='member.php'?'active':'' ?>">
            <i class="bi bi-person-badge"></i> Member
        </a>
        <a href="<?= $base ?>/pages/paket.php" class="nav-link <?= $page==='paket.php'?'active':'' ?>">
            <i class="bi bi-box"></i> Paket
        </a>
        <a href="<?= $base ?>/pages/transaksi.php" class="nav-link <?= $page==='transaksi.php'?'active':'' ?>">
            <i class="bi bi-receipt"></i> Transaksi
        </a>
        <?php else: ?>
        <a href="<?= $base ?>/dashboard.php" class="nav-link <?= $page==='dashboard.php'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="<?= $base ?>/pages/users.php" class="nav-link <?= $page==='users.php'?'active':'' ?>">
            <i class="bi bi-people"></i> Users
        </a>
        <?php endif; ?>
        <a href="<?= $base ?>/pages/outlet.php" class="nav-link <?= $page==='outlet.php'?'active':'' ?>">
            <i class="bi bi-shop"></i> Outlet
        </a>
        <a href="<?= $base ?>/pages/member.php" class="nav-link <?= $page==='member.php'?'active':'' ?>">
            <i class="bi bi-person-badge"></i> Member
        </a>
        <a href="<?= $base ?>/pages/paket.php" class="nav-link <?= $page==='paket.php'?'active':'' ?>">
            <i class="bi bi-box"></i> Paket
        </a>
        <a href="<?= $base ?>/pages/transaksi.php" class="nav-link <?= $page==='transaksi.php'?'active':'' ?>">
            <i class="bi bi-receipt"></i> Transaksi
        </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info d-flex align-items-center gap-2">
            <div class="bg-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px">
                <i class="bi bi-person-fill text-white"></i>
            </div>
            <div>
                <span><?= htmlspecialchars($_SESSION['user_nama']) ?></span>
                <small><?= ucfirst($_SESSION['user_role']) ?></small>
            </div>
        </div>
        <a href="<?= $base ?>/logout.php" class="btn btn-sm btn-outline-light w-100 mt-2">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>
