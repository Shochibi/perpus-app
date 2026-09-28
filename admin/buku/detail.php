<?php

require_once '../../middleware/admin.php';
require_once '../../config/koneksi.php';

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

$query = mysqli_query(
    $koneksi,
    "
    SELECT
        tbl_buku.*,
        tbl_kategori.nama_kategori
    FROM tbl_buku
    LEFT JOIN tbl_kategori
        ON tbl_buku.id_kategori =
           tbl_kategori.id_kategori
    WHERE tbl_buku.id_buku = $id
    "
);

$buku = mysqli_fetch_assoc($query);

if (!$buku) {
    die("Buku tidak ditemukan.");
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($buku['judul_buku'], ENT_QUOTES, 'UTF-8') ?> — Detail Buku</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
</head>
<body class="admin-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/sidebar.php"; ?>
    <main class="main admin-book-detail">
        <div class="admin-page-heading"><div><span class="admin-kicker">MANAJEMEN KOLEKSI / DETAIL BUKU</span><h1>Detail buku</h1><p>Informasi lengkap koleksi perpustakaan.</p></div><a class="admin-add-book__back" href="index.php">← Kembali ke daftar</a></div>
        <div class="admin-book-detail__layout">
            <div class="admin-book-detail__main">
                <section class="admin-add-book__section"><div class="admin-add-book__section-head"><span>01</span><div><h2>Informasi buku</h2><p>Data buku yang tersimpan di katalog.</p></div></div>
                    <h2 class="admin-book-detail__title"><?= htmlspecialchars($buku['judul_buku']) ?></h2>
                    <p class="admin-book-detail__byline">Ditulis oleh <?= htmlspecialchars($buku['penulis_buku']) ?></p>
                    <dl class="admin-book-detail__facts">
                        <div><dt>Penulis</dt><dd><?= htmlspecialchars($buku['penulis_buku']) ?></dd></div>
                        <div><dt>Penerbit</dt><dd><?= htmlspecialchars($buku['penerbit_buku'] ?: 'Belum diisi') ?></dd></div>
                        <div><dt>Kategori</dt><dd><?= htmlspecialchars($buku['nama_kategori'] ?? 'Belum diisi') ?></dd></div>
                        <div><dt>Tahun terbit</dt><dd><?= htmlspecialchars($buku['tahun_terbit']) ?></dd></div>
                    </dl>
                </section>
                <section class="admin-add-book__section"><div class="admin-add-book__section-head"><span>02</span><div><h2>Deskripsi</h2><p>Ringkasan isi buku.</p></div></div><p class="admin-book-detail__description"><?= !empty($buku['deskripsi']) ? nl2br(htmlspecialchars($buku['deskripsi'])) : 'Belum ada deskripsi untuk buku ini.' ?></p></section>
            </div>
            <aside class="admin-book-detail__side">
                <section class="admin-add-book__section admin-book-detail__cover-card"><div class="admin-add-book__section-head"><span>▤</span><div><h2>Cover buku</h2><p>Tampilan buku di katalog.</p></div></div><div class="admin-book-detail__cover"><?php if (!empty($buku['cover_buku'])): ?><img src="<?= BASE_URL ?>/uploads/cover/<?= rawurlencode($buku['cover_buku']) ?>" alt="Cover <?= htmlspecialchars($buku['judul_buku'], ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><span>▤<small>Belum ada cover</small></span><?php endif; ?></div></section>
                <section class="admin-add-book__section"><div class="admin-add-book__section-head"><span>▥</span><div><h2>File bacaan</h2><p>Dokumen PDF buku.</p></div></div><?php if (!empty($buku['pdf_buku'])): ?><a class="admin-book-detail__pdf" href="<?= BASE_URL ?>/uploads/pdf/<?= rawurlencode($buku['pdf_buku']) ?>" target="_blank" rel="noopener">▥ &nbsp; Buka PDF buku ↗</a><?php else: ?><p class="admin-book-detail__muted">Belum ada file PDF untuk buku ini.</p><?php endif; ?></section>
                <a class="admin-book-detail__edit" href="edit.php?id=<?= (int)$buku['id_buku'] ?>">✎ &nbsp; Edit buku</a>
            </aside>
        </div>
    </main>
</body>
</html>
