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
        <a href="<?= $base ?>/pages/pesanan.php" class="nav-link <?= $page==='pesanan.php'?'active':'' ?>">
            <i class="bi bi-basket2"></i> Pesanan
            <?php
            $db_tmp = new mysqli('localhost','root','','R_laundry');
            $n = $db_tmp->query("SELECT COUNT(*) as c FROM pesanan WHERE status='menunggu'")->fetch_assoc()['c'];
            $db_tmp->close();
            if ($n > 0): ?>
            <span class="badge bg-danger ms-auto" style="font-size:.65rem"><?= $n ?></span>
            <?php endif; ?>
        </a>
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
