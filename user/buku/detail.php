<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
$id = (int)($_GET["id"] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT b.*,k.nama_kategori FROM tbl_buku b LEFT JOIN tbl_kategori k ON k.id_kategori=b.id_kategori WHERE id_buku=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$b) exit("Buku tidak ditemukan");
$idUser = (int)($_SESSION["id_user"] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT f.id_buku, rp.halaman_terakhir, rp.total_halaman FROM tbl_buku b LEFT JOIN tbl_favorit f ON f.id_buku=b.id_buku AND f.id_user=? LEFT JOIN reading_progress rp ON rp.id_buku=b.id_buku AND rp.id_user=? WHERE b.id_buku=?");
mysqli_stmt_bind_param($stmt, "iii", $idUser, $idUser, $id); mysqli_stmt_execute($stmt);
$state = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: []; mysqli_stmt_close($stmt);
$isFavorite = !empty($state["id_buku"]);
?>
<!doctype html>
<html lang="id" class="detail-document">

<head>
    <meta charset="utf-8">
    <title><?= e($b["judul_buku"]) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/notification.css">
</head>

<body class="user-page detail-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    <main class="container detail"><img src="<?= BASE_URL ?>/uploads/cover/<?= e($b["cover_buku"]) ?>">
        <section>
            <h1><?= e($b["judul_buku"]) ?></h1>
            <p>Penulis: <?= e($b["penulis_buku"]) ?></p>
            <p>Kategori: <?= e($b["nama_kategori"] ?? "-") ?></p>
            <p><?= nl2br(e($b["deskripsi"])) ?></p>
            <?php if (!empty($b["pdf_buku"])): ?>
                <a class="button" href="<?= BASE_URL ?>/user/buku/read.php?id=<?= $id ?>"><?= !empty($state["halaman_terakhir"]) ? "Lanjutkan dari halaman ".(int)$state["halaman_terakhir"] : "Mulai Membaca" ?></a>
            <?php else: ?><b>PDF belum tersedia.</b><?php endif; ?>
            <form method="post" action="<?= BASE_URL ?>/user/buku/favorit.php" style="margin-top:12px">
                <input type="hidden" name="id_buku" value="<?= $id ?>">
                <input type="hidden" name="aksi" value="<?= $isFavorite ? "hapus" : "tambah" ?>">
                <button class="button" type="submit"><?= $isFavorite ? "Hapus dari Favorit" : "Tambahkan ke Favorit" ?></button>
            </form>
        </section>
    </main>
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html>
