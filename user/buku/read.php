<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";

$id = (int)($_GET['id'] ?? 0);
$idUser = (int)$_SESSION['id_user'];

$stmt = mysqli_prepare($koneksi, "SELECT * FROM tbl_buku WHERE id_buku=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$b) exit("Buku tidak ditemukan");
if (empty($b['pdf_buku'])) exit("File PDF untuk buku ini tidak tersedia. <a href='".BASE_URL."/user/buku/detail.php?id=$id'>Kembali</a>");

$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) n FROM tbl_detail_peminjaman d JOIN tbl_peminjaman p ON p.id_peminjaman=d.id_peminjaman WHERE p.id_anggota=? AND d.id_buku=? AND p.status='dipinjam'");
mysqli_stmt_bind_param($stmt, "ii", $idUser, $id);
mysqli_stmt_execute($stmt);
$borrowed = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["n"];
mysqli_stmt_close($stmt);

if (!$borrowed) exit("Kamu belum meminjam buku ini. <a href='".BASE_URL."/user/buku/detail.php?id=$id'>Kembali</a>");

$pdfUrl = BASE_URL . "/uploads/pdf/" . $b['pdf_buku'];
$absPath = PDF_DIR . $b['pdf_buku'];
if (!file_exists($absPath)) exit("File PDF tidak ditemukan di server.");
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Baca: <?= e($b['judul_buku']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
    <style>
        .reader { width: 100%; }
    </style>
</head>

<body class="user-page">
    <?php include __DIR__ . "/../partials/header.php"; ?>
    <main class="container">
        <h1><?= e($b['judul_buku']) ?></h1>
        <div class="card reader">
            <embed src="<?= $pdfUrl ?>" type="application/pdf" width="100%" height="800px">
            </embed>
        </div>
    </main>
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html>
