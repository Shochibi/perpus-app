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
    <title><?= htmlspecialchars($buku['judul_buku']); ?></title>
</head>
<body>
    <h1>
        <?= htmlspecialchars($buku['judul_buku']); ?>
    </h1>
    <?php if (!empty($buku['cover_buku'])): ?>

        <img
            src="../../uploads/cover/<?= htmlspecialchars($buku['cover_buku']); ?>"
            width="200">

    <?php endif; ?>
    <p>
        <strong>Penulis:</strong>
        <?= htmlspecialchars($buku['penulis_buku']); ?>
    </p>
    <p>
        <strong>Penerbit:</strong>
        <?= htmlspecialchars($buku['penerbit_buku']); ?>
    </p>
    <p>
        <strong>Kategori:</strong>
        <?= htmlspecialchars($buku['nama_kategori'] ?? '-'); ?>
    </p>
    <p>
        <strong>Tahun Terbit:</strong>
        <?= htmlspecialchars($buku['tahun_terbit']); ?>
    </p>
    <p>
        <strong>Stok:</strong>
        <?= htmlspecialchars($buku['stok']); ?>
    </p>
    <p>
        <strong>Deskripsi:</strong>
    </p>
    <p>
        <?= nl2br(htmlspecialchars($buku['deskripsi'])); ?>
    </p>
    <?php if (!empty($buku['pdf_buku'])): ?>
        <p>
            <a
                href="../../uploads/pdf/<?= htmlspecialchars($buku['pdf_buku']); ?>"
                target="_blank">
                📖 Baca Buku
            </a>
        </p>
    <?php endif; ?>
    <br>
    <a href="edit.php?id=<?= $buku['id_buku']; ?>">
        Edit
    </a>
    |
    <a href="index.php">
        Kembali
    </a>
</body>

</html>