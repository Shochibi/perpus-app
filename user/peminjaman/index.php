<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
$id = (int)$_SESSION["id_user"];
$q = trim($_GET["q"] ?? "");
$like = "%$q%";
$stmt = mysqli_prepare($koneksi, "SELECT p.id_peminjaman, p.tanggal_pinjam, p.tanggal_tenggat, p.tanggal_kembali, p.status, b.judul_buku, b.penulis_buku, b.cover_buku FROM tbl_peminjaman p JOIN tbl_detail_peminjaman d ON d.id_peminjaman=p.id_peminjaman JOIN tbl_buku b ON b.id_buku=d.id_buku WHERE p.id_anggota=? AND (b.judul_buku LIKE ? OR b.penulis_buku LIKE ?) ORDER BY p.id_peminjaman DESC, b.judul_buku ASC");
mysqli_stmt_bind_param($stmt, "iss", $id, $like, $like);
mysqli_stmt_execute($stmt);
$loans = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Peminjaman</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
</head>

<body class="user-page catalog-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    <main class="catalog-main loan-main">
        <form class="catalog-search loan-catalog-search" method="get">
            <label class="catalog-search__field">
                <span aria-hidden="true">⌕</span>
                <input name="q" value="<?= e($q) ?>" placeholder="Cari buku..." aria-label="Cari buku">
            </label>
        </form>
        <section class="loan-grid" aria-label="Daftar peminjaman">
            <?php if (mysqli_num_rows($loans) === 0): ?>
                <p class="empty-loans">Belum ada peminjaman yang sesuai.</p>
            <?php endif; ?>
            <?php while ($p = mysqli_fetch_assoc($loans)): ?>
                <?php
                $daysLeft = (int)(new DateTimeImmutable("today"))->diff(new DateTimeImmutable($p["tanggal_tenggat"]))->format("%r%a");
                $dueText = $daysLeft < 0 ? "Terlambat " . abs($daysLeft) . " hari" : "Sisa waktu pinjam " . $daysLeft . " hari lagi";
                ?>
                <article class="loan-card">
                    <?php if (!empty($p["cover_buku"])): ?>
                        <img src="<?= BASE_URL ?>/uploads/cover/<?= e($p["cover_buku"]) ?>" alt="Cover <?= e($p["judul_buku"]) ?>">
                    <?php else: ?>
                        <div class="loan-cover-fallback" aria-label="Cover tidak tersedia">📖</div>
                    <?php endif; ?>
                    <div class="loan-info">
                        <h2><?= e($p["judul_buku"]) ?></h2>
                        <p class="loan-author"><?= e($p["penulis_buku"]) ?></p>
                        <p class="loan-due"><?= $p["status"] === "selesai" ? "Sudah dikembalikan" : e($dueText) ?></p>
                        <span class="loan-status <?= e($p["status"]) ?>"><?= e(ucfirst($p["status"])) ?></span>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
    </main>
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html>