<?php
require_once __DIR__ . "/../middleware/user.php";
require_once __DIR__ . "/../config/koneksi.php";
$booksResult = mysqli_query($koneksi, "SELECT b.*,k.nama_kategori FROM tbl_buku b LEFT JOIN tbl_kategori k ON k.id_kategori=b.id_kategori ORDER BY b.id_buku DESC LIMIT 6");
$latestBooks = [];
while ($book = mysqli_fetch_assoc($booksResult)) {
    $latestBooks[] = $book;
}
$sliderImages = [
    ["file" => "bg4.png", "alt" => "Pilihan buku pertama"],
    ["file" => "slider-2.png", "alt" => "Pilihan buku kedua"],
    ["file" => "slider-3.png", "alt" => "Pilihan buku ketiga"],
    ["file" => "slider-4.png", "alt" => "Pilihan buku keempat"],
];
$sliderSlides = array_chunk($sliderImages, 2);

ensure_reading_progress($koneksi);
$lastReadBook = null;
$lastReadStmt = mysqli_prepare($koneksi, "SELECT b.*, rp.halaman_terakhir FROM reading_progress rp JOIN tbl_buku b ON b.id_buku=rp.id_buku WHERE rp.id_user=? ORDER BY rp.updated_at DESC LIMIT 1");
if ($lastReadStmt) {
    mysqli_stmt_bind_param($lastReadStmt, "i", $_SESSION["id_user"]);
    mysqli_stmt_execute($lastReadStmt);
    $lastReadBook = mysqli_fetch_assoc(mysqli_stmt_get_result($lastReadStmt)) ?: null;
    mysqli_stmt_close($lastReadStmt);
}
$bookCountResult = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_buku");
$bookCount = (int)(mysqli_fetch_assoc($bookCountResult)["total"] ?? 0);
$dashboardUser = null;
$userStmt = mysqli_prepare($koneksi, "SELECT is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=?");
mysqli_stmt_bind_param($userStmt, "i", $_SESSION["id_user"]);
mysqli_stmt_execute($userStmt);
$dashboardUser = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));
mysqli_stmt_close($userStmt);
$dashboardPremium = false;
if ($dashboardUser && $dashboardUser["is_premium"] && !empty($dashboardUser["tanggal_premium_hingga"])) {
    $dashboardPremium = new DateTime("today") <= new DateTime($dashboardUser["tanggal_premium_hingga"]);
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Perpustakaan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../css/footer.css") ?>">
</head>

<body class="user-page dashboard-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/partials/header.php"; ?>
    <main class="container dashboard-container">
        <section class="dashboard-slider" aria-label="Pilihan buku">
            <div class="dashboard-slider__track">
                <?php foreach ($sliderSlides as $slideIndex => $slideImages): ?>
                    <article class="dashboard-slide <?= $slideIndex === 0 ? "is-active" : "" ?>">
                        <div class="dashboard-slide__covers">
                            <?php foreach ($slideImages as $image): ?>
                                <a class="dashboard-slide__cover" href="<?= BASE_URL ?>/user/buku/index.php">
                                    <img src="<?= BASE_URL ?>/img/<?= e($image["file"]) ?>" alt="<?= e($image["alt"]) ?>">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if (count($sliderSlides) > 1): ?>
                <button class="dashboard-slider__control dashboard-slider__control--prev" type="button" aria-label="Buku sebelumnya"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                <button class="dashboard-slider__control dashboard-slider__control--next" type="button" aria-label="Buku berikutnya"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                <div class="dashboard-slider__dots" aria-label="Pilih slide">
                    <?php foreach ($sliderSlides as $slideIndex => $slideImages): ?><button type="button" class="<?= $slideIndex === 0 ? "is-active" : "" ?>" aria-label="Slide <?= $slideIndex + 1 ?>"></button><?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-reading">
            <div class="dashboard-section-heading">
                <div><p class="dashboard-kicker">LANJUTKAN MEMBACA</p><h2>Buku terakhir dibaca</h2></div>
            </div>
            <?php if ($lastReadBook): ?>
                <article class="dashboard-reading__card">
                    <a class="dashboard-reading__cover" href="<?= BASE_URL ?>/user/buku/read.php?id=<?= (int)$lastReadBook["id_buku"] ?>">
                        <?php if (!empty($lastReadBook["cover_buku"])): ?><img src="<?= BASE_URL ?>/uploads/cover/<?= e($lastReadBook["cover_buku"]) ?>" alt="Cover <?= e($lastReadBook["judul_buku"]) ?>"><?php else: ?><span><i class="fa-solid fa-book-open" aria-hidden="true"></i></span><?php endif; ?>
                    </a>
                    <div class="dashboard-reading__info">
                        <span class="dashboard-reading__eyebrow">Terakhir dibuka</span>
                        <h3><?= e($lastReadBook["judul_buku"]) ?></h3>
                        <p><?= e($lastReadBook["penulis_buku"]) ?></p>
                        <div class="dashboard-reading__progress"><span style="width: <?= $dashboardPremium ? "100" : min(100, ((int)$lastReadBook["halaman_terakhir"] / 10) * 100) ?>%"></span></div>
                        <small>Halaman <?= (int)$lastReadBook["halaman_terakhir"] ?><?= $dashboardPremium ? "" : " dari 10 preview" ?></small>
                    </div>
                    <a class="dashboard-reading__button" href="<?= BASE_URL ?>/user/buku/read.php?id=<?= (int)$lastReadBook["id_buku"] ?>">Lanjut baca <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </article>
            <?php else: ?>
                <div class="dashboard-reading__empty"><i class="fa-solid fa-book-open-reader" aria-hidden="true"></i><div><strong>Belum ada buku yang dibaca</strong><p>Mulai membaca buku pilihanmu dan progresnya akan muncul di sini.</p></div><a href="<?= BASE_URL ?>/user/buku/index.php">Cari buku</a></div>
            <?php endif; ?>
        </section>

        <section class="dashboard-latest">
            <div class="dashboard-section-heading dashboard-latest-heading">
                <div><p class="dashboard-kicker">KOLEKSI TERBARU</p><h2>Buku untuk dibaca hari ini</h2></div>
                <span><?= number_format($bookCount, 0, ",", ".") ?> judul tersedia</span>
            </div>
            <div class="dashboard-book-grid">
                <?php foreach ($latestBooks as $b): ?>
                            <article class="catalog-book">
                                <a class="catalog-book__cover" href="<?= BASE_URL ?>/user/buku/detail.php?id=<?= (int)$b["id_buku"] ?>">
                                    <?php if (!empty($b["cover_buku"])): ?><img src="<?= BASE_URL ?>/uploads/cover/<?= e($b["cover_buku"]) ?>" alt="Cover <?= e($b["judul_buku"]) ?>" loading="lazy">
                                    <?php else: ?><span><i class="fa-solid fa-book-open" aria-hidden="true"></i></span><?php endif; ?>
                                </a>
                                <a class="catalog-book__body" href="<?= BASE_URL ?>/user/buku/detail.php?id=<?= (int)$b["id_buku"] ?>">
                                    <h2><?= e($b["judul_buku"]) ?></h2>
                                    <p><?= e($b["penulis_buku"]) ?></p>
                                    <span class="catalog-stock <?= (int)$b["stok"] > 0 ? "is-available" : "is-empty" ?>">
                                        <?= ((int)$b["stok"] > 0) ? "Tersedia" : "Tidak tersedia" ?>
                                    </span>
                                </a>
                            </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php include __DIR__ . "/partials/footer.php"; ?>
    
<!-- Tombol untuk membuka manual book -->
<button class="manual-book-btn" id="openManualBook" type="button">
    <i class="fa-solid fa-book-open"></i>
    Panduan
</button>

<!-- Popup Manual Book -->
<div class="manual-book-overlay" id="manualBookPopup">
    <div class="manual-book-modal">

        <button class="manual-book-close" id="closeManualBook"
                type="button" aria-label="Tutup">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="manual-book-header">
            <span class="manual-book-label">MANUAL BOOK</span>
            <h2>Panduan Perpustakaan</h2>
            <p>
                Selamat datang! Ikuti panduan berikut untuk
                menggunakan website perpustakaan.
            </p>
        </div>

        <div class="manual-book-content">

            <div class="manual-step">
                <div class="manual-step-number">1</div>
                <div>
                    <h3>Jelajahi Koleksi Buku</h3>
                    <p>
                        Pilih menu buku untuk melihat koleksi,
                        mencari judul, dan membaca detail buku.
                    </p>
                </div>
            </div>

            <div class="manual-step">
                <div class="manual-step-number">2</div>
                <div>
                    <h3>Baca Buku</h3>
                    <p>
                        Buka detail buku, lalu pilih tombol baca
                        untuk mulai membaca buku yang tersedia.
                    </p>
                </div>
            </div>

            <div class="manual-step">
                <div class="manual-step-number">3</div>
                <div>
                    <h3>Lanjutkan Membaca</h3>
                    <p>
                        Gunakan bagian "Buku terakhir dibaca"
                        untuk melanjutkan bacaan sebelumnya.
                    </p>
                </div>
            </div>

            <div class="manual-step">
                <div class="manual-step-number">4</div>
                <div>
                    <h3>Baca Buku</h3>
                    <p>
                        Buka detail buku, lalu pilih tombol baca
                        untuk mulai membaca buku yang tersedia.
                    </p>
                </div>
            </div>

            <div class="manual-step">
                <div class="manual-step-number">5</div>
                <div>
                    <h3>Lanjutkan Membaca</h3>
                    <p>
                        Gunakan bagian "Buku terakhir dibaca"
                        untuk melanjutkan bacaan sebelumnya.
                    </p>
                </div>
            </div>

            <div class="manual-step">
                <div class="manual-step-number">6</div>
                <div>
                    <h3>Gunakan Fitur Premium</h3>
                    <p>
                        Jika tersedia, kamu dapat melihat informasi
                        premium untuk mengetahui fasilitas tambahan.
                    </p>
                </div>
            </div>

        </div>

        <div class="manual-book-footer">
            <button type="button" id="finishManualBook">
                Mengerti, mulai membaca
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>

    </div>
</div>
    <?php if (count($sliderSlides) > 1): ?>
        <script>
            const slides = [...document.querySelectorAll(".dashboard-slide")];
            const dots = [...document.querySelectorAll(".dashboard-slider__dots button")];
            let activeSlide = 0;
            const showSlide = (index) => {
                activeSlide = (index + slides.length) % slides.length;
                slides.forEach((slide, slideIndex) => slide.classList.toggle("is-active", slideIndex === activeSlide));
                dots.forEach((dot, dotIndex) => dot.classList.toggle("is-active", dotIndex === activeSlide));
            };
            document.querySelector(".dashboard-slider__control--prev").addEventListener("click", () => showSlide(activeSlide - 1));
            document.querySelector(".dashboard-slider__control--next").addEventListener("click", () => showSlide(activeSlide + 1));
            dots.forEach((dot, dotIndex) => dot.addEventListener("click", () => showSlide(dotIndex)));
            setInterval(() => showSlide(activeSlide + 1), 6000);
        </script>
        
<script>
    const manualPopup = document.getElementById("manualBookPopup");
    const openManual = document.getElementById("openManualBook");
    const closeManual = document.getElementById("closeManualBook");
    const finishManual = document.getElementById("finishManualBook");

    function showManualBook() {
        manualPopup.classList.add("active");
    }

    function hideManualBook() {
        manualPopup.classList.remove("active");
    }

    // Otomatis muncul satu kali per sesi tab
    if (!sessionStorage.getItem("manualBookShown")) {
        showManualBook();
        sessionStorage.setItem("manualBookShown", "true");
    }

    // Tombol untuk membuka kembali
    openManual.addEventListener("click", showManualBook);

    // Tombol tutup
    closeManual.addEventListener("click", hideManualBook);
    finishManual.addEventListener("click", hideManualBook);

    // Tutup jika klik area luar popup
    manualPopup.addEventListener("click", function (event) {
        if (event.target === manualPopup) {
            hideManualBook();
        }
    });

    // Tutup dengan tombol Escape
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            hideManualBook();
        }
    });
</script>
    <?php endif; ?>
</body>

</html>