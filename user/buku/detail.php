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
// cek apakah user sedang meminjam buku ini
$borrowed = 0;
$idUser = (int)($_SESSION["id_user"] ?? 0);
if ($idUser) {
    $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) n FROM tbl_detail_peminjaman d JOIN tbl_peminjaman p ON p.id_peminjaman=d.id_peminjaman WHERE p.id_anggota=? AND d.id_buku=? AND p.status='dipinjam'");
    mysqli_stmt_bind_param($stmt, "ii", $idUser, $id);
    mysqli_stmt_execute($stmt);
    $borrowed = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["n"];
    mysqli_stmt_close($stmt);
}
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
            <p>Stok: <?= $b["stok"] ?></p>
            <p><?= nl2br(e($b["deskripsi"])) ?></p>
            <?php if ($borrowed > 0): ?>
                <a class="button" href="<?= BASE_URL ?>/user/buku/read.php?id=<?= $id ?>">Baca Buku</a>
            <?php else: ?>
                <?php if ($b["stok"] > 0): ?>
                    <a class="button" href="<?= BASE_URL ?>/user/peminjaman/pinjam.php?id=<?= $id ?>">Pinjam Buku</a>
                <?php else: ?>
                    <b>Tidak tersedia</b>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html>