<?php
require_once __DIR__ . "/../middleware/user.php";
require_once __DIR__ . "/../config/koneksi.php";
$books = mysqli_query($koneksi, "SELECT b.*,k.nama_kategori FROM tbl_buku b LEFT JOIN tbl_kategori k ON k.id_kategori=b.id_kategori ORDER BY b.id_buku DESC LIMIT 8");
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Perpustakaan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
</head>

<body>
    <header class="header"><a href="<?= BASE_URL ?>/user/dashboard.php">📚 Perpustakaan</a>
        <nav><a href="<?= BASE_URL ?>/user/buku/index.php">Buku</a><a href="<?= BASE_URL ?>/user/peminjaman/index.php">Peminjaman Saya</a><span><?= e($_SESSION["fullname"]) ?></span><a href="<?= BASE_URL ?>/auth/logout.php">Logout</a></nav>
    </header>
    <main class="container">
        <h1>Temukan Buku</h1>
        <p>Selamat datang, <?= e($_SESSION["fullname"]) ?></p>
        <div class="grid"><?php while ($b = mysqli_fetch_assoc($books)): ?><article class="book"><img src="<?= BASE_URL ?>/uploads/cover/<?= e($b["cover_buku"]) ?>">
                    <h3><?= e($b["judul_buku"]) ?></h3>
                    <p><?= e($b["penulis_buku"]) ?></p><span><?= ((int)$b["stok"] > 0) ? "Tersedia: " . $b["stok"] : "Tidak tersedia" ?></span><a href="<?= BASE_URL ?>/user/buku/detail.php?id=<?= $b["id_buku"] ?>">Detail</a>
                </article><?php endwhile; ?></div>
    </main>
</body>

</html>