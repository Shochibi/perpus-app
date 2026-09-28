<?php $adminPath = $_SERVER["PHP_SELF"] ?? ""; ?>
<aside class="sidebar">
    <a class="sidebar__brand" href="<?= BASE_URL ?>/admin/dashboard.php">
        <span class="sidebar__logo" aria-hidden="true">+</span>
        <span><strong>perpus.com</strong><small>ADMIN</small></span>
    </a>
    <nav>
        <a class="<?= str_contains($adminPath, "/admin/dashboard.php") ? "is-active" : "" ?>" href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
        <a class="<?= str_contains($adminPath, "/admin/buku/") ? "is-active" : "" ?>" href="<?= BASE_URL ?>/admin/buku/index.php">Buku</a>
        <!-- <a href="<?= BASE_URL ?>/admin/kategori/index.php">Kategori</a> -->
        <a class="<?= str_contains($adminPath, "/admin/laporan/") ? "is-active" : "" ?>" href="<?= BASE_URL ?>/admin/laporan/index.php">Laporan</a>
        <!-- <a href="<?= BASE_URL ?>/admin/user/index.php">User</a> -->
        <a class="<?= str_contains($adminPath, "/admin/pembayaran/") ? "is-active" : "" ?>" href="<?= BASE_URL ?>/admin/pembayaran/index.php">Pembayaran</a>
        <!-- <a class="<?= str_contains($adminPath, "/admin/peminjaman/") ? "is-active" : "" ?>" href="<?= BASE_URL ?>/admin/peminjaman/index.php">Peminjaman</a> -->
    </nav>
    <a class="logout" href="<?= BASE_URL ?>/auth/logout.php" onclick="return confirm('Yakin ingin logout?')">Keluar akun</a>
</aside>