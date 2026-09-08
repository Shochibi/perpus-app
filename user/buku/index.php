<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
$q = trim($_GET["q"] ?? "");
if ($q !== "") {
    $like = "%$q%";
    $stmt = mysqli_prepare($koneksi, "SELECT b.* FROM tbl_buku b WHERE b.judul_buku LIKE ? OR b.penulis_buku LIKE ? ORDER BY b.id_buku DESC");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
</head>

<body class="user-page catalog-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    <main class="catalog-main">
        <div class="catalog-toolbar">
            <h1>Daftar Buku</h1>
            <form class="catalog-search" method="get">
                <label class="catalog-search__field">
                    <span aria-hidden="true">⌕</span>
                    <input name="q" value="<?= e($q) ?>" placeholder="Cari buku..." aria-label="Cari buku">
                </label>
            </form>
        </div>
        <div class="catalog-grid"><?php while ($b = mysqli_fetch_assoc($books)): ?>
            <a class="catalog-book" href="<?= BASE_URL ?>/user/buku/detail.php?id=<?= (int)$b["id_buku"] ?>">
                <div class="catalog-book__cover">
                    <img src="<?= BASE_URL ?>/uploads/cover/<?= e($b["cover_buku"]) ?>" alt="Cover <?= e($b["judul_buku"]) ?>">
                </div>
                <div class="catalog-book__body">
                    <h2><?= e($b["judul_buku"]) ?></h2>
                    <p><?= e($b["penulis_buku"]) ?></p>
                    <span class="catalog-stock <?= (int)$b["stok"] > 0 ? "is-available" : "is-empty" ?>">
                        <?= ((int)$b["stok"] > 0) ? "Tersedia" : "Tidak tersedia" ?>
                    </span>
                </div>
            </a>
        <?php endwhile; ?>
        </div>
    </main>
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html> 