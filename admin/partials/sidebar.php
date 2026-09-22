<aside class="sidebar">
    <h2>📚 Perpustakaan</h2><small>ADMIN</small>
    <nav>
        <a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/buku/index.php">Buku</a>
        <!-- <a href="<?= BASE_URL ?>/admin/kategori/index.php">Kategori</a> -->
        <a href="<?= BASE_URL ?>/admin/laporan/index.php">Laporan</a>
        <!-- <a href="<?= BASE_URL ?>/admin/user/index.php">User</a> -->
        <a href="<?= BASE_URL ?>/admin/pembayaran/index.php">Pembayaran</a>
    </nav>
    <a class="logout" href="<?= BASE_URL ?>/auth/logout.php" onclick="return confirm('Yakin ingin logout?')">Logout</a>
</aside>