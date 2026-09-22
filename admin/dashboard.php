<?php
require_once __DIR__ . "/../middleware/admin.php";
require_once __DIR__ . "/../config/koneksi.php";
$books = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) n FROM tbl_buku"))["n"];
$users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) n FROM tbl_user WHERE role='user'"))["n"];
$readers = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT id_user) n FROM reading_progress"))["n"];
$finished = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) n FROM reading_progress WHERE status='selesai'"))["n"];
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
</head>

<body><?php render_flash(); ?><?php include __DIR__ . "/partials/sidebar.php"; ?><main class="main">
        <h1>Dashboard Admin</h1>
        <p>Halo, <?= e($_SESSION["fullname"]) ?></p>
        <div class="cards">
            <div class="card"><b>Judul Buku</b><strong><?= $books ?></strong></div>
            <div class="card"><b>Pembaca Aktif</b><strong><?= $readers ?></strong></div>
            <div class="card"><b>Anggota</b><strong><?= $users ?></strong></div>
            <div class="card"><b>Buku Selesai Dibaca</b><strong><?= $finished ?></strong></div>
        </div>
    </main>
</body>

</html>
