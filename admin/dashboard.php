<?php
require_once __DIR__ . "/../middleware/admin.php";
require_once __DIR__ . "/../config/koneksi.php";
$books = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) n FROM tbl_buku"))["n"];
$users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) n FROM tbl_user WHERE role='user'"))["n"];
$loans = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) n FROM tbl_peminjaman WHERE status='dipinjam'"))["n"];
$latest = mysqli_query($koneksi, "SELECT id_buku, judul_buku, penulis_buku, cover_buku FROM tbl_buku ORDER BY id_buku DESC LIMIT 4");
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../css/app.css") ?>">
</head>

<body class="admin-page"><?php render_flash(); ?><?php include __DIR__ . "/partials/sidebar.php"; ?><main class="main">
        <div class="admin-page-heading dashboard-heading">
            <div><span class="admin-kicker">RUANG KERJA ADMIN</span><h1>Selamat datang, <?= e($_SESSION["fullname"]) ?> 👋</h1><p>Pantau koleksi dan aktivitas perpustakaan dari satu tempat.</p></div>
            <span class="admin-date"><?= e(date("d M Y")) ?></span>
        </div>
        <section class="admin-hero" aria-label="Akses cepat"><div><span class="admin-hero__eyebrow">KELOLA PERPUSTAKAAN</span><h2>Koleksi rapi, pembaca nyaman.</h2><p>Tambahkan buku baru, periksa katalog, dan pantau laporan aktivitas dengan mudah.</p><a class="admin-hero__button" href="<?= BASE_URL ?>/admin/buku/tambah.php">＋ Tambah buku baru <span>→</span></a></div><div class="admin-hero__art" aria-hidden="true"><span>▤</span><span>▥</span><span>▦</span></div></section>
        <section class="admin-overview" aria-label="Ringkasan perpustakaan"><div class="admin-section-title"><div><span class="admin-kicker">SEKILAS PERPUSTAKAAN</span><h2>Ringkasan data</h2></div></div>
            <div class="cards admin-stat-grid">
                <div class="card admin-stat-card"><span class="admin-stat-card__icon">▤</span><span class="admin-stat-card__label">Judul buku</span><strong><?= number_format((int)$books, 0, ',', '.') ?></strong><small>Koleksi dalam katalog</small></div>
                <div class="card admin-stat-card"><span class="admin-stat-card__icon">♙</span><span class="admin-stat-card__label">Anggota</span><strong><?= number_format((int)$users, 0, ',', '.') ?></strong><small>Akun pembaca</small></div>
                <div class="card admin-stat-card"><span class="admin-stat-card__icon">↗</span><span class="admin-stat-card__label">Sedang dipinjam</span><strong><?= number_format((int)$loans, 0, ',', '.') ?></strong><small>Peminjaman aktif</small></div>
            </div>
        </section>
        <section class="admin-dashboard-bottom"><div class="admin-recent card"><div class="admin-section-title"><div><span class="admin-kicker">BARU DITAMBAHKAN</span><h2>Koleksi terbaru</h2></div><a href="<?= BASE_URL ?>/admin/buku/index.php">Lihat semua →</a></div>
            <?php if ($latest && mysqli_num_rows($latest)): while ($book = mysqli_fetch_assoc($latest)): ?><a class="admin-recent__book" href="<?= BASE_URL ?>/admin/buku/detail.php?id=<?= (int)$book['id_buku'] ?>"><span class="admin-recent__cover"><?php if ($book['cover_buku']): ?><img src="<?= BASE_URL ?>/uploads/cover/<?= e($book['cover_buku']) ?>" alt=""><?php else: ?>▤<?php endif; ?></span><span class="admin-recent__info"><strong><?= e($book['judul_buku']) ?></strong><small><?= e($book['penulis_buku']) ?></small></span></a><?php endwhile; else: ?><p class="admin-empty">Belum ada buku dalam katalog.</p><?php endif; ?></div>
            <div class="admin-shortcuts card"><div class="admin-section-title"><div><span class="admin-kicker">AKSES CEPAT</span><h2>Yang ingin dikerjakan?</h2></div></div><a href="<?= BASE_URL ?>/admin/buku/index.php"><span>▤</span><span><strong>Kelola buku</strong><small>Lihat, ubah, dan susun katalog</small></span>→</a><a href="<?= BASE_URL ?>/admin/laporan/index.php"><span>▥</span><span><strong>Lihat laporan</strong><small>Pantau aktivitas pengguna</small></span>→</a><a href="<?= BASE_URL ?>/admin/pembayaran/index.php"><span>◇</span><span><strong>Cek pembayaran</strong><small>Kelola permintaan premium</small></span>→</a></div></section>
    </main>
</body>

</html>