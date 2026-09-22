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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($buku['judul_buku']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-buku.css">
</head>
<body>
    <?php include __DIR__ . "/../partials/sidebar.php"; ?>
    <main class="main book-admin"><header class="book-admin__top"><div><h1>Detail Buku</h1><p>Informasi lengkap koleksi buku digital.</p></div><a class="book-admin__button" href="index.php"><i class="fa-solid fa-arrow-left"></i> Kembali</a></header>
    <article class="book-detail-card"><div class="book-detail-card__cover"><?php if(!empty($buku['cover_buku'])):?><img src="<?= BASE_URL ?>/uploads/cover/<?= e($buku['cover_buku']) ?>" alt="Cover <?= e($buku['judul_buku']) ?>"><?php else:?><div class="book-detail-card__placeholder"><i class="fa-solid fa-book"></i></div><?php endif;?></div><div><span class="book-admin__badge"><?= e($buku['nama_kategori']??'Tanpa kategori') ?></span><h2><?= e($buku['judul_buku']) ?></h2><p class="book-admin__sub">oleh <?= e($buku['penulis_buku']) ?></p><div class="book-detail-card__meta"><div><small>Penerbit</small><strong><?= e($buku['penerbit_buku']?:'-') ?></strong></div><div><small>Tahun terbit</small><strong><?= e($buku['tahun_terbit']?:'-') ?></strong></div><div><small>File digital</small><strong><?= empty($buku['pdf_buku'])?'Belum tersedia':'PDF tersedia' ?></strong></div><div><small>ID Buku</small><strong>#<?= (int)$buku['id_buku'] ?></strong></div></div><h3>Deskripsi</h3><p class="book-detail-card__description"><?= nl2br(e($buku['deskripsi']?:'Belum ada deskripsi.')) ?></p><div class="book-detail-card__actions"><a class="primary" href="edit.php?id=<?= (int)$buku['id_buku'] ?>"><i class="fa-regular fa-pen-to-square"></i> Edit Buku</a><?php if(!empty($buku['pdf_buku'])):?><a class="secondary" href="<?= BASE_URL ?>/uploads/pdf/<?= e($buku['pdf_buku']) ?>" target="_blank"><i class="fa-regular fa-file-pdf"></i> Lihat PDF</a><?php endif;?></div></div></article></main>
</body>

</html>
