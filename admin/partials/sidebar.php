<?php $adminPath = $_SERVER["PHP_SELF"] ?? ""; ?>
<aside class="sidebar admin-sidebar" id="admin-sidebar">
    <a class="sidebar__brand" href="<?= BASE_URL ?>/admin/dashboard.php">
        <span class="sidebar__logo" aria-hidden="true"><img src="<?= BASE_URL ?>/img/library-logo.svg" alt=""></span>
        <span><strong>perpus.com</strong><small>PANEL ADMIN</small></span>
    </a>
    <div class="admin-sidebar__caption">MENU UTAMA</div>
    <nav aria-label="Navigasi admin">
        <a class="<?= str_contains($adminPath, "/admin/dashboard.php") ? "is-active" : "" ?>" href="<?= BASE_URL ?>/admin/dashboard.php"><span aria-hidden="true">▦</span> Dashboard</a>
        <a class="<?= str_contains($adminPath, "/admin/buku/") ? "is-active" : "" ?>" href="<?= BASE_URL ?>/admin/buku/index.php"><span aria-hidden="true">▤</span> Kelola buku</a>
        <a class="<?= str_contains($adminPath, "/admin/laporan/") ? "is-active" : "" ?>" href="<?= BASE_URL ?>/admin/laporan/index.php"><span aria-hidden="true">▥</span> Laporan</a>
        <a class="<?= str_contains($adminPath, "/admin/pembayaran/") ? "is-active" : "" ?>" href="<?= BASE_URL ?>/admin/pembayaran/index.php"><span aria-hidden="true">◇</span> Pembayaran</a>
    </nav>
    <div class="admin-sidebar__bottom">
        <div class="admin-sidebar__profile"><span class="admin-sidebar__avatar"><?= e(strtoupper(substr($_SESSION["fullname"] ?? "A", 0, 1))) ?></span><span><strong><?= e($_SESSION["fullname"] ?? "Admin") ?></strong><small>Administrator</small></span></div>
        <a class="logout" href="<?= BASE_URL ?>/auth/logout.php" onclick="return confirm('Yakin ingin logout?')">↗ &nbsp; Keluar akun</a>
    </div>
</aside>
