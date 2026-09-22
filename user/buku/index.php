<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
$q = trim($_GET["q"] ?? "");
if ($q !== "") {
    $like = "%$q%";
    $stmt = mysqli_prepare($koneksi, "SELECT b.*, k.nama_kategori, bm.id_bookmark FROM tbl_buku b LEFT JOIN tbl_kategori k ON k.id_kategori=b.id_kategori LEFT JOIN tbl_bookmark bm ON bm.id_buku=b.id_buku AND bm.id_user=? WHERE b.judul_buku LIKE ? OR b.penulis_buku LIKE ? OR k.nama_kategori LIKE ? ORDER BY b.id_buku DESC");
    mysqli_stmt_bind_param($stmt, "isss", $_SESSION["id_user"], $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $books = mysqli_stmt_get_result($stmt);
} else {
    $idUser = (int)$_SESSION["id_user"];
    $books = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, bm.id_bookmark FROM tbl_buku b LEFT JOIN tbl_kategori k ON k.id_kategori=b.id_kategori LEFT JOIN tbl_bookmark bm ON bm.id_buku=b.id_buku AND bm.id_user=$idUser ORDER BY b.id_buku DESC");
}
$booksByCategory = [];
while ($book = mysqli_fetch_assoc($books)) {
    $categoryName = $q !== "" ? "__search" : ($book["nama_kategori"] ?: "Umum");
    $booksByCategory[$categoryName][] = $book;
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
    <style>
        body.user-page.catalog-page,
        body.user-page.catalog-page > main {
            background: #ffffff;
        }

        .catalog-main {
            width: min(1280px, calc(100% - 32px));
            margin: 24px auto 40px;
            padding: 18px 18px 28px;
            background: #ffffff;
            border-bottom: 0;
        }

        .catalog-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            margin: 0 auto 28px;
            width: min(1180px, 100%);
        }

        .catalog-toolbar h1 {
            display: block;
            margin: 0;
            color: #173042;
            font-size: clamp(1.8rem, 2vw, 2.4rem);
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .catalog-grid {
            display: flex;
            flex: 1;
            min-width: 0;
            gap: 18px;
            overflow-x: auto;
            width: 100%;
            margin: 0 auto;
            padding: 4px 2px 14px;
            scroll-snap-type: x mandatory;
            scroll-behavior: auto;
            scroll-padding-inline: 2px;
            scrollbar-width: none;
        }

        .catalog-grid::-webkit-scrollbar {
            display: none;
        }

        .catalog-row-wrap {
            position: relative;
        }

        .catalog-row-control {
            display: grid;
            position: absolute;
            top: 50% !important;
            bottom: auto !important;
            margin: 0;
            width: 50px;
            height: 50px;
            place-items: center;
            border: 1px solid #d7e5e2;
            border-radius: 50%;
            background: #ffffff;
            color: #176b69;
            cursor: pointer;
            box-shadow: 0 5px 12px rgba(23, 107, 105, .08);
            opacity: 0;
            pointer-events: none;
            font-size: 1rem;
            transition: opacity .18s ease, background-color .18s ease, border-color .18s ease;
            transform: translateY(-50%);
            z-index: 3;
        }

        .catalog-row-control--prev,
        .catalog-row-control--next {
            top: 50% !important;
            bottom: auto !important;
            width: 44px;
            height: 44px;
            margin: 0;
            transform: translateY(-50%);
        }

        .catalog-row-control--prev { left: -22px; }
        .catalog-row-control--next { right: -17px; }

        .catalog-row-wrap:hover .catalog-row-control,
        .catalog-row-wrap:focus-within .catalog-row-control {
            opacity: 1;
            pointer-events: auto;
        }

        .catalog-row-wrap:hover .catalog-row-control:disabled,
        .catalog-row-wrap:focus-within .catalog-row-control:disabled {
            opacity: 0;
            pointer-events: none;
        }

        .catalog-row-control:hover:not(:disabled) {
            border-color: #176b69;
            background: #edf7f5;
            transform: translateY(-50%);
        }

        .catalog-row-control:disabled {
            opacity: 0;
            pointer-events: none;
            cursor: not-allowed;
        }

        .catalog-row-control.is-hidden {
            display: none !important;
        }

        .catalog-row-control[hidden] {
            display: none !important;
        }

        .catalog-row-control:not(.is-available) {
            display: none !important;
        }

        .catalog-row-wrap.is-at-start .catalog-row-control--prev,
        .catalog-row-wrap.is-at-end .catalog-row-control--next {
            display: none !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .catalog-category {
            width: min(1180px, 100%);
            margin: 0 auto 28px;
        }

        .catalog-category.is-hidden {
            display: none;
        }

        .catalog-empty {
            width: min(1180px, 100%);
            margin: 34px auto;
            color: #718191;
            text-align: center;
        }

        .catalog-load-more {
            display: flex;
            justify-content: center;
            margin: 8px auto 12px;
        }

        .catalog-load-more button {
            padding: 10px 18px;
            border: 1px solid #b9cfcc;
            border-radius: 8px;
            background: #edf7f5;
            color: #176b69;
            font: inherit;
            font-size: .84rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
        }

        .catalog-load-more button:hover {
            border-color: #176b69;
            background: #dcefeb;
            transform: translateY(-1px);
        }

        .catalog-category > h2 {
            margin: 0 0 12px;
            color: #52636c;
            font-size: 1rem;
            font-weight: 800;
        }

        .catalog-book {
            display: flex;
            min-width: 0;
            overflow: hidden;
            flex-direction: column;
            border: 1px solid #e7ebee;
            border-radius: 16px;
            background: #ffffff;
            color: inherit;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
            text-decoration: none;
            flex: 0 0 calc((100% - 90px) / 6);
            scroll-snap-align: start;
            scroll-snap-stop: always;
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
            padding-top: 6px;
        }

        .catalog-book:hover {
            border-color: #c9deda;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.11);
            transform: translateY(-4px);
        }

        .catalog-book__cover {
            position: relative;
            height: 200px;
            padding: 14px 8px 4px;
            overflow: hidden;
            background: #ffffff;
            transform: translateY(2px);
        }

        .catalog-book__bookmark {
            position: absolute;
            top: 0;
            right: 8px;
            z-index: 2;
            margin: 0;
        }

        .catalog-book__bookmark-button {
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            padding: 0;
            border: 0;
            background: transparent;
            color: #5d6c76;
            font-size: 1.8rem;
            cursor: pointer;
            -webkit-text-stroke: 1px #111820;
        }

        .catalog-book__bookmark-button.is-saved {
            color: #e2ae32;
        }

        .catalog-book__cover img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 11px;
            transform: none;
        }

        .catalog-book__cover > span {
            display: grid;
            height: 100%;
            place-items: center;
            border-radius: 11px;
            background: #edf7f5;
            color: #176b69;
            font-size: 2rem;
        }

        .catalog-book__body {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 4px;
            min-height: 92px;
            padding: 9px 10px 11px;
            color: inherit;
            text-decoration: none;
        }

        .catalog-book h2,
        .catalog-book p {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .catalog-book h2 {
            margin: 0;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            color: #173042;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .catalog-book p {
            margin: 0;
            color: #718191;
            font-size: 0.68rem;
            line-height: 1.4;
            white-space: nowrap;
        }

        .catalog-stock {
            align-self: flex-start;
            width: fit-content;
            margin-top: auto;
            padding: 4px 8px;
            border-radius: 999px;
            background: #eafaf6;
            color: #0f766e;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .catalog-stock.is-empty {
            background: #fff0f0;
            color: #b42318;
        }

        @media (max-width: 1150px) {
            .catalog-book { flex-basis: calc((100% - 72px) / 5); }
        }

        @media (max-width: 900px) {
            .catalog-book { flex-basis: calc((100% - 54px) / 4); }
            .catalog-toolbar { flex-direction: column; align-items: flex-start; }
            .catalog-search { width: 100%; }
        }

        @media (max-width: 640px) {
            .catalog-main {
                width: min(100% - 16px, 100%);
                padding: 18px 16px 28px;
            }

            .catalog-book { flex-basis: calc((100% - 18px) / 2); }
        }
    </style>
</head>

<body class="user-page catalog-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    <main class="catalog-main">
        <?php if ($q !== "" && count($booksByCategory) === 0): ?>
            <p class="catalog-empty">Buku dengan kata "<?= e($q) ?>" tidak ditemukan.</p>
        <?php endif; ?>
        <?php $categoryPosition = 0; ?>
        <?php foreach ($booksByCategory as $categoryName => $categoryBooks): ?>
            <section class="catalog-category <?= $q !== "" ? "catalog-search-results" : "" ?> <?= $categoryPosition >= 3 && $q === "" ? "is-hidden" : "" ?>" aria-label="<?= $q !== "" ? "Hasil pencarian buku" : "Kategori " . e($categoryName) ?>">
                <?php if ($q === ""): ?>
                    <h2><?= e($categoryName) ?></h2>
                <?php endif; ?>
                <div class="catalog-row-wrap is-at-start">
                    <button class="catalog-row-control catalog-row-control--prev" type="button" aria-label="Buku <?= e($categoryName) ?> sebelumnya" hidden><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                    <div class="catalog-grid">
                <?php foreach ($categoryBooks as $b): ?>
            <article class="catalog-book">
                <div class="catalog-book__cover">
                    <form class="catalog-book__bookmark" method="post" action="<?= BASE_URL ?>/user/buku/toggle_bookmark.php">
                        <input type="hidden" name="id_buku" value="<?= (int)$b["id_buku"] ?>">
                        <input type="hidden" name="return_to" value="index.php">
                        <button type="submit" class="catalog-book__bookmark-button <?= !empty($b["id_bookmark"]) ? "is-saved" : "" ?>" aria-label="<?= !empty($b["id_bookmark"]) ? "Hapus dari bookmark" : "Simpan ke bookmark" ?>" title="<?= !empty($b["id_bookmark"]) ? "Hapus dari bookmark" : "Simpan ke bookmark" ?>">
                            <i class="fa-solid fa-bookmark" aria-hidden="true"></i>
                        </button>
                    </form>
                    <?php if (!empty($b["cover_buku"])): ?>
                        <img src="<?= BASE_URL ?>/uploads/cover/<?= e($b["cover_buku"]) ?>" alt="Cover <?= e($b["judul_buku"]) ?>">
                    <?php else: ?>
                        <span aria-label="Cover tidak tersedia"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
                    <?php endif; ?>
                </div>
                <a class="catalog-book__body" href="<?= BASE_URL ?>/user/buku/detail.php?id=<?= (int)$b["id_buku"] ?>">
                    <h2><?= e($b["judul_buku"]) ?></h2>
                    <p><?= e($b["penulis_buku"]) ?></p>
                </a>
            </article>
                <?php endforeach; ?>
                    </div>
                    <button class="catalog-row-control catalog-row-control--next <?= count($categoryBooks) > 6 ? "is-available" : "" ?>" type="button" aria-label="Buku <?= e($categoryName) ?> berikutnya" <?= count($categoryBooks) <= 6 ? "hidden" : "" ?>><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </div>
            </section>
            <?php $categoryPosition++; ?>
        <?php endforeach; ?>
        <?php if (count($booksByCategory) > 3): ?>
            <div class="catalog-load-more">
                <button type="button" id="catalogLoadMore">Muat lebih banyak</button>
            </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . "/../partials/footer.php"; ?>
    <script>
        const loadMoreButton = document.getElementById("catalogLoadMore");
        if (loadMoreButton) {
            loadMoreButton.addEventListener("click", () => {
                const hiddenCategories = [...document.querySelectorAll(".catalog-category.is-hidden")];
                hiddenCategories.slice(0, 3).forEach((category) => {
                    category.classList.remove("is-hidden");
                    category.querySelectorAll(".catalog-row-wrap").forEach(initializeRow);
                });
                if (document.querySelectorAll(".catalog-category.is-hidden").length === 0) {
                    loadMoreButton.closest(".catalog-load-more").remove();
                }
            });
        }

        const initializeRow = (row) => {
            const grid = row.querySelector(".catalog-grid");
            const previousButton = row.querySelector(".catalog-row-control--prev");
            const nextButton = row.querySelector(".catalog-row-control--next");
            let animationFrame = null;
            const updateButtons = () => {
                const isAtStart = Math.round(grid.scrollLeft) === 0;
                const isAtEnd = grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 1;
                row.classList.toggle("is-at-start", isAtStart);
                row.classList.toggle("is-at-end", isAtEnd);
                previousButton.disabled = isAtStart;
                nextButton.disabled = isAtEnd;
                previousButton.classList.toggle("is-hidden", isAtStart);
                nextButton.classList.toggle("is-hidden", isAtEnd);
                previousButton.classList.toggle("is-available", !isAtStart);
                nextButton.classList.toggle("is-available", !isAtEnd);
                previousButton.hidden = isAtStart;
                nextButton.hidden = isAtEnd;
                previousButton.style.display = isAtStart ? "none" : "grid";
                nextButton.style.display = isAtEnd ? "none" : "grid";
            };
            const moveRow = (direction) => {
                const distance = Math.max(1, grid.clientWidth - 4);
                const start = grid.scrollLeft;
                const target = Math.max(0, Math.min(start + (distance * direction), grid.scrollWidth - grid.clientWidth));
                const duration = 520;
                const startedAt = performance.now();
                if (animationFrame) cancelAnimationFrame(animationFrame);
                grid.style.scrollSnapType = "none";

                const animate = (now) => {
                    const progress = Math.min(1, (now - startedAt) / duration);
                    const eased = progress < .5
                        ? 4 * progress * progress * progress
                        : 1 - Math.pow(-2 * progress + 2, 3) / 2;
                    grid.scrollLeft = start + ((target - start) * eased);
                    if (progress < 1) {
                        animationFrame = requestAnimationFrame(animate);
                    } else {
                        animationFrame = null;
                        grid.style.scrollSnapType = "x mandatory";
                        updateButtons();
                    }
                };
                animationFrame = requestAnimationFrame(animate);
            };
            previousButton.addEventListener("click", () => moveRow(-1));
            nextButton.addEventListener("click", () => moveRow(1));
            grid.addEventListener("scroll", updateButtons, { passive: true });
            window.addEventListener("resize", updateButtons);
            updateButtons();
        };

        document.querySelectorAll(".catalog-row-wrap").forEach(initializeRow);
    </script>
</body>

</html> 