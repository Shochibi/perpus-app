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

// cek status premium user
$stmt = mysqli_prepare($koneksi, "SELECT is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=?");
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$isPremium = false;
if ($user && $user["is_premium"]) {
    $today = new DateTime("today");
    $expiryDate = new DateTime($user["tanggal_premium_hingga"]);
    $isPremium = $today <= $expiryDate;
}

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
        <?php if (!$isPremium): ?>
            <div class="card" style="background-color: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <p style="margin: 0; color: #856404;"><strong>📖 Preview Mode</strong> - Anda melihat 10 halaman pertama sebagai preview. Upgrade ke Premium untuk akses penuh ke semua halaman.</p>
                <a href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php" style="display: inline-block; margin-top: 10px; padding: 8px 15px; background-color: #ffc107; color: #000; text-decoration: none; border-radius: 3px;">Upgrade ke Premium</a>
            </div>
        <?php endif; ?>
        <div class="card reader">
            <embed src="<?= $isPremium ? $pdfUrl : $pdfUrl . '#page=1&view=FitH' ?>" type="application/pdf" width="100%" height="800px">
            </embed>
            <?php if (!$isPremium): ?>
                <div style="padding: 20px; background-color: #f8f9fa; text-align: center; margin-top: 20px; border-top: 1px solid #dee2e6;">
                    <p style="color: #666; margin-bottom: 10px;">Halaman yang ditampilkan: 1 - 10 (Preview)</p>
                    <a href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php" class="button">Beli Premium untuk Akses Penuh</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html>
