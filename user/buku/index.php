<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
$q = trim($_GET["q"] ?? "");
if ($q) {
    $like = "%$q%";
    $stmt = mysqli_prepare($koneksi, "SELECT b.* FROM tbl_buku b WHERE b.judul LIKE ? OR b.penulis LIKE ? ORDER BY b.id_buku DESC");
    mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    mysqli_stmt_execute($stmt);
    $books = mysqli_stmt_get_result($stmt);
} else {
    $books = mysqli_query($koneksi, "SELECT * FROM tbl_buku ORDER BY id_buku DESC");
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Buku</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
</head>

<body>
    <header class="header"><a href="<?= BASE_URL ?>/user/dashboard.php">📚 Perpustakaan</a>
        <nav><a href="<?= BASE_URL ?>/user/peminjaman/index.php">Peminjaman Saya</a><a href="<?= BASE_URL ?>/auth/logout.php">Logout</a></nav>
    </header>
    <main class="container">
        <h1>Daftar Buku</h1>
        <form class="search">
            <input name="q" value="<?= e($q) ?>" placeholder="Cari buku...">
            <button>Cari</button>
        </form>
        <div class="grid"><?php while ($b = mysqli_fetch_assoc($books)): ?>
                <article class="book"><img src="<?= BASE_URL ?>/uploads/cover/<?= e($b["cover_buku"]) ?>">
                    <h3><?= e($b["judul_buku"]) ?></h3>
                    <p><?= e($b["penulis_buku"]) ?></p><span><?= ((int)$b["stok"] > 0) ? "Tersedia: " . $b["stok"] : "Tidak tersedia" ?></span>
                    <a href="<?= BASE_URL ?>/user/buku/detail.php?id=<?= $b["id_buku"] ?>">Detail</a>
                </article><?php endwhile; ?>
        </div>
    </main>
</body>

</html>