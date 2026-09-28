<?php
require_once __DIR__ . "/../middleware/admin.php";
require_once __DIR__ . "/../config/koneksi.php";
$books = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) n FROM tbl_buku"))["n"];
$users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) n FROM tbl_user WHERE role='user'"))["n"];
$loans = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) n FROM tbl_peminjaman WHERE status='dipinjam'"))["n"];
$stock = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(stok),0) n FROM tbl_buku"))["n"];
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../css/app.css") ?>">
</head>

<body><?php render_flash(); ?><?php include __DIR__ . "/partials/sidebar.php"; ?><main class="main">
        <div class="admin-page-heading dashboard-heading">
            <div>
                <span class="admin-kicker">RUANG KERJA ADMIN</span>
                <h1>Dashboard</h1>
                <p>Halo, <?= e($_SESSION["fullname"]) ?>. Berikut ringkasan perpustakaan hari ini.</p>
            </div>
        </div>
        <div class="cards admin-stat-grid">
            <div class="card admin-stat-card"><span class="admin-stat-card__label">Judul buku</span><strong><?= $books ?></strong><small>Koleksi tersedia</small></div>
            <div class="card admin-stat-card"><span class="admin-stat-card__label">Total stok</span><strong><?= $stock ?></strong><small>Eksemplar di katalog</small></div>
            <div class="card admin-stat-card"><span class="admin-stat-card__label">Anggota</span><strong><?= $users ?></strong><small>Akun pengguna aktif</small></div>
            <div class="card admin-stat-card"><span class="admin-stat-card__label">Sedang dipinjam</span><strong><?= $loans ?></strong><small>Perlu dipantau</small></div>
        </div>
    </main>
</body>

</html>