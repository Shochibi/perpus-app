<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";

$idUser = (int)$_SESSION["id_user"];
$stmt = mysqli_prepare($koneksi, "SELECT b.id_buku, b.judul_buku, b.penulis_buku, b.cover_buku, bm.created_at FROM tbl_bookmark bm JOIN tbl_buku b ON b.id_buku=bm.id_buku WHERE bm.id_user=? ORDER BY bm.created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$bookmarks = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bookmark Buku</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
    <style>
        .loan-card > a { display: block; flex: 0 0 70px; }
        .loan-card > a img { display: block; }
        .loan-info h2 a { color: inherit; text-decoration: none; }
        .loan-info h2 a:hover { text-decoration: underline; }
        .bookmark-main { padding: 0; }
        .bookmark-list { grid-template-columns: 1fr; width: 100%; margin: 0; gap: 0; }
        .bookmark-list .loan-card { border-radius: 0; }
        .bookmark-list .loan-card { min-height: 150px; padding: 10px 16px; gap: 18px; }
        .bookmark-list .loan-card > a { flex-basis: 94px; }
        .bookmark-list .loan-card img,
        .bookmark-list .loan-card .loan-cover-fallback { width: 94px; height: 130px; flex-basis: 94px; }
    </style>
</head>
<body class="user-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    <main class="catalog-main loan-main bookmark-main">
        <section class="loan-grid bookmark-list" aria-label="Daftar bookmark">
            <?php if (mysqli_num_rows($bookmarks) === 0): ?>
                <p class="empty-loans">Belum ada bookmark yang sesuai.</p>
            <?php endif; ?>
            <?php while ($book = mysqli_fetch_assoc($bookmarks)): ?>
                <article class="loan-card">
                    <a href="<?= BASE_URL ?>/user/buku/detail.php?id=<?= (int)$book["id_buku"] ?>">
                        <?php if (!empty($book["cover_buku"])): ?>
                            <img src="<?= BASE_URL ?>/uploads/cover/<?= e($book["cover_buku"]) ?>" alt="Cover <?= e($book["judul_buku"]) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="loan-cover-fallback" aria-label="Cover tidak tersedia">📖</div>
                        <?php endif; ?>
                    </a>
                    <div class="loan-info">
                        <h2><a href="<?= BASE_URL ?>/user/buku/detail.php?id=<?= (int)$book["id_buku"] ?>"><?= e($book["judul_buku"]) ?></a></h2>
                        <p class="loan-author"><?= e($book["penulis_buku"]) ?></p>
                        <p class="loan-due">Disimpan <?= date("d M Y", strtotime($book["created_at"])) ?></p>
                        <span class="loan-status dipinjam">Tersimpan</span>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
    </main>
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>
</html>
