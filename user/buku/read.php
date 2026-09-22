<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

$id = (int)($_GET['id'] ?? 0);
$idUser = (int)$_SESSION['id_user'];

$stmt = mysqli_prepare($koneksi, "SELECT * FROM tbl_buku WHERE id_buku=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$b) exit("Buku tidak ditemukan");

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "save_progress") {
    save_reading_progress($koneksi, $idUser, $id, max(1, (int)($_POST["page"] ?? 1)));
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["success" => true]);
    exit;
}

if (empty($b['pdf_buku'])) exit("File PDF untuk buku ini tidak tersedia. <a href='".BASE_URL."/user/buku/detail.php?id=$id'>Kembali</a>");

ensure_reading_progress($koneksi);
$lastReadPage = 1;
$progressStmt = mysqli_prepare($koneksi, "SELECT halaman_terakhir FROM reading_progress WHERE id_user=? AND id_buku=? LIMIT 1");
if ($progressStmt) {
    mysqli_stmt_bind_param($progressStmt, "ii", $idUser, $id);
    mysqli_stmt_execute($progressStmt);
    $progressRow = mysqli_fetch_assoc(mysqli_stmt_get_result($progressStmt));
    $lastReadPage = max(1, (int)($progressRow["halaman_terakhir"] ?? 1));
    mysqli_stmt_close($progressStmt);
}

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

$stmt = mysqli_prepare($koneksi, "SELECT id_bookmark FROM tbl_bookmark WHERE id_user=? AND id_buku=?");
mysqli_stmt_bind_param($stmt, "ii", $idUser, $id);
mysqli_stmt_execute($stmt);
$isBookmarked = (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$pdfUrl = BASE_URL . "/uploads/pdf/" . $b['pdf_buku'];
$absPath = PDF_DIR . $b['pdf_buku'];
if (!file_exists($absPath)) exit("File PDF tidak ditemukan di server.");

log_activity($koneksi, $idUser, $id, "baca", "Membaca buku: " . $b['judul_buku']);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Baca: <?= e($b['judul_buku']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        .reader { width: 100%; }
        .reader:fullscreen { overflow: auto; padding: 8px; background: #f3f5f5; }
        .reader:fullscreen .preview-pages { padding: 8px; }
        .reader:fullscreen .preview-track { min-height: calc(100vh - 88px); }
        .reader:fullscreen .preview-page { min-height: calc(100vh - 88px); }
        .reader:fullscreen .preview-page canvas { width: auto !important; height: min(calc(100vh - 104px), calc((100vw - 40px) * 1.35)) !important; }
        .preview-pages { position: relative; box-sizing: border-box; overflow: hidden; padding: 18px; background: #f3f5f5; touch-action: pan-y; user-select: none; }
        .preview-track { display: flex; align-items: flex-start; box-sizing: border-box; min-height: 420px; visibility: hidden; transition: transform .28s ease; cursor: grab; }
        .preview-track.is-ready { visibility: visible; }
        .preview-track.is-dragging { transition: none; cursor: grabbing; }
        .preview-page { flex: 0 0 50%; display: flex; align-items: flex-start; justify-content: center; box-sizing: border-box; width: 50%; min-width: 0; min-height: 420px; padding: 0; }
        .preview-page:nth-child(odd) { justify-content: flex-end; }
        .preview-page:nth-child(even) { justify-content: flex-start; }
        .preview-page canvas { display: block; width: min(100%, 360px); height: auto; image-rendering: auto; background: #fff; box-shadow: 0 3px 12px rgba(23, 48, 66, .12); }
        .preview-loading, .preview-error { padding: 28px; color: #52636c; text-align: center; }
        .preview-loading { width: 100%; }
        .preview-navigation { display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 14px; }
        .preview-navigation button { width: 36px; height: 36px; border: 1px solid #d5e0de; border-radius: 50%; background: #fff; color: #176b69; cursor: pointer; }
        .preview-navigation .preview-fullscreen { width: auto; padding: 0 12px; border-radius: 7px; font-weight: 700; }
        .preview-navigation .preview-mark { width: auto; padding: 0 12px; border-radius: 7px; font-weight: 700; }
        .preview-navigation button:disabled { opacity: .4; cursor: not-allowed; }
        .preview-counter { min-width: 72px; color: #52636c; font-size: .9rem; text-align: center; }
        .reader-upgrade { padding: 22px 10px 8px; text-align: center; scroll-margin-top: 18px; }
        .reader-upgrade[hidden] { display: none; }
        .reader-upgrade p { margin: 0 0 12px; color: #52636c; }
        .reader-upgrade__button { display: inline-flex; align-items: center; gap: 9px; padding: 10px 16px; border: 1px solid #b9cfcc; border-radius: 7px; background: #edf7f5; color: #176b69; font-weight: 700; text-decoration: none; transition: background .2s ease, border-color .2s ease, transform .2s ease; }
        .reader-upgrade__button:hover { border-color: #176b69; background: #dcefeb; color: #125654; transform: translateY(-1px); }
        .reader-upgrade__crown { display: inline-grid; flex: 0 0 24px; width: 24px; height: 24px; place-items: center; border-radius: 50%; background: #e2ae32; color: #fff; }
        .reader-upgrade__crown i { color: #fff; font-size: .72rem; }
        .reader-toolbar { display: flex; justify-content: flex-end; margin-bottom: 12px; }
        .bookmark-button { padding: 9px 14px; border: 1px solid #d5e0de; border-radius: 7px; background: transparent; color: #52636c; font: inherit; font-weight: 600; cursor: pointer; }
        .bookmark-button:hover, .bookmark-button.is-saved { border-color: #e2ae32; background: #fff9e8; color: #9a6d0d; }
    </style>
</head>

<body class="user-page">
    <?php include __DIR__ . "/../partials/header.php"; ?>
    <main class="container">
        <h1><?= e($b['judul_buku']) ?></h1>
        <div class="reader-toolbar">
            <form method="post" action="<?= BASE_URL ?>/user/buku/toggle_bookmark.php">
                <input type="hidden" name="id_buku" value="<?= $id ?>">
                <input type="hidden" name="return_to" value="read.php?id=<?= $id ?>">
                <button type="submit" class="bookmark-button <?= $isBookmarked ? "is-saved" : "" ?>">
                    <?= $isBookmarked ? "Tersimpan di Bookmark" : "Simpan ke Bookmark" ?>
                </button>
            </form>
        </div>
        <div class="reader">
            <div id="previewPages" class="preview-pages" data-pdf-url="<?= e($pdfUrl) ?>">
                <div class="preview-loading">Memuat buku...</div>
            </div>
            <?php if (!$isPremium): ?>
                <div class="reader-upgrade" id="readerUpgrade" hidden>
                    <p>Upgrade untuk membaca seluruh isi buku.</p>
                    <a href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php" class="reader-upgrade__button">
                        <span class="reader-upgrade__crown"><i class="fa-solid fa-crown" aria-hidden="true"></i></span>
                        Upgrade - Rp 14.999
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include __DIR__ . "/../partials/footer.php"; ?>
    <script>
        const previewPages = document.getElementById("previewPages");
        const previewUrl = previewPages.dataset.pdfUrl;
        pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

        pdfjsLib.getDocument(previewUrl).promise.then(async (pdf) => {
            previewPages.innerHTML = "";
            const pageLimit = <?= $isPremium ? "pdf.numPages" : "Math.min(pdf.numPages, " . (int)MAX_PREVIEW_PAGES . ")" ?>;
            const track = document.createElement("div");
            const navigation = document.createElement("div");
            const previousButton = document.createElement("button");
            const nextButton = document.createElement("button");
            const fullscreenButton = document.createElement("button");
            const markButton = document.createElement("button");
            const counter = document.createElement("span");
            const upgradePrompt = document.getElementById("readerUpgrade");
            let currentPage = 0;
            let dragStartX = 0;
            let isDragging = false;
            const savedPage = <?= (int)$lastReadPage ?>;
            let markedPage = 0;
            let upgradePromptShown = false;
            const pageWrappers = [];
            const renderedPages = new Set();

            track.className = "preview-track";
            navigation.className = "preview-navigation";
            previousButton.type = "button";
            previousButton.innerHTML = "<i class=\"fa-solid fa-chevron-left\" aria-hidden=\"true\"></i>";
            previousButton.setAttribute("aria-label", "Halaman sebelumnya");
            nextButton.type = "button";
            nextButton.innerHTML = "<i class=\"fa-solid fa-chevron-right\" aria-hidden=\"true\"></i>";
            nextButton.setAttribute("aria-label", "Halaman berikutnya");
            fullscreenButton.innerHTML = "<i class=\"fa-solid fa-expand\" aria-hidden=\"true\"></i> Fullscreen";
            fullscreenButton.setAttribute("aria-label", "Buka fullscreen");
            fullscreenButton.className = "preview-fullscreen";
            markButton.type = "button";
            markButton.innerHTML = "<i class=\"fa-solid fa-bookmark\" aria-hidden=\"true\"></i> Tandai halaman ini";
            markButton.setAttribute("aria-label", "Tandai halaman terakhir dibaca");
            markButton.className = "preview-mark";
            counter.className = "preview-counter";
            navigation.append(previousButton, counter, nextButton, markButton, fullscreenButton);
            previewPages.append(track, navigation);

            const lastSpreadStart = pageLimit % 2 === 0 ? pageLimit - 2 : pageLimit - 1;
            markedPage = Math.max(0, Math.min(savedPage - 1, lastSpreadStart));
            const saveProgress = (page) => {
                fetch(window.location.href, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({ action: "save_progress", page: String(page) })
                }).catch(() => {});
            };

            const updatePage = (pageIndex) => {
                currentPage = Math.max(0, Math.min(pageIndex - (pageIndex % 2), lastSpreadStart));
                track.style.transform = `translateX(-${(currentPage / 2) * 100}%)`;
                counter.textContent = pageLimit === 1
                    ? "1 / 1"
                    : `${currentPage + 1}-${Math.min(currentPage + 2, pageLimit)} / ${pageLimit}`;
                previousButton.disabled = currentPage === 0;
                nextButton.disabled = currentPage === lastSpreadStart;
                markButton.innerHTML = currentPage === markedPage
                    ? "<i class=\"fa-solid fa-check\" aria-hidden=\"true\"></i> Halaman ditandai"
                    : "<i class=\"fa-solid fa-bookmark\" aria-hidden=\"true\"></i> Tandai halaman ini";

                if (upgradePrompt && currentPage === lastSpreadStart && !upgradePromptShown) {
                    upgradePromptShown = true;
                    upgradePrompt.hidden = false;
                    requestAnimationFrame(() => upgradePrompt.scrollIntoView({ behavior: "smooth", block: "center" }));
                }
            };

            updatePage(savedPage - 1);

            const goToSpread = async (pageIndex) => {
                const targetPage = Math.max(0, Math.min(pageIndex - (pageIndex % 2), lastSpreadStart));
                await renderPage(targetPage + 1);
                if (targetPage + 2 <= pageLimit) await renderPage(targetPage + 2);
                alignSpread(targetPage);
                updatePage(targetPage + 1);
            };

            previousButton.addEventListener("click", () => goToSpread(currentPage - 2));
            nextButton.addEventListener("click", () => goToSpread(currentPage + 2));
            markButton.addEventListener("click", () => {
                markedPage = currentPage;
                saveProgress(currentPage + 1);
                markButton.innerHTML = "<i class=\"fa-solid fa-check\" aria-hidden=\"true\"></i> Halaman ditandai";
            });
            fullscreenButton.addEventListener("click", async () => {
                if (document.fullscreenElement) {
                    await document.exitFullscreen();
                } else {
                    await document.querySelector(".reader").requestFullscreen();
                }
            });
            track.addEventListener("pointerdown", (event) => {
                isDragging = true;
                dragStartX = event.clientX;
                track.classList.add("is-dragging");
                track.setPointerCapture(event.pointerId);
            });
            track.addEventListener("pointerup", (event) => {
                if (!isDragging) return;
                isDragging = false;
                track.classList.remove("is-dragging");
                const dragDistance = event.clientX - dragStartX;
                if (Math.abs(dragDistance) > 50) goToSpread(currentPage + (dragDistance < 0 ? 2 : -2));
            });
            track.addEventListener("pointercancel", () => {
                isDragging = false;
                track.classList.remove("is-dragging");
            });

            for (let pageNumber = 1; pageNumber <= pageLimit; pageNumber += 1) {
                const wrapper = document.createElement("div");
                wrapper.className = "preview-page";
                pageWrappers[pageNumber] = wrapper;
                track.appendChild(wrapper);
            }

            const renderPage = async (pageNumber) => {
                if (renderedPages.has(pageNumber)) return;
                const page = await pdf.getPage(pageNumber);
                const baseViewport = page.getViewport({ scale: 1 });
                const scale = Math.min(2, 760 / baseViewport.width);
                const viewport = page.getViewport({ scale });
                const canvas = document.createElement("canvas");
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                pageWrappers[pageNumber].appendChild(canvas);
                await page.render({ canvasContext: canvas.getContext("2d"), viewport }).promise;
                renderedPages.add(pageNumber);
            };

            const alignSpread = (spreadStart) => {
                const pageNumbers = [spreadStart + 1, spreadStart + 2].filter((pageNumber) => pageNumber <= pageLimit);
                const canvases = pageNumbers
                    .map((pageNumber) => pageWrappers[pageNumber].querySelector("canvas"))
                    .filter(Boolean);
                if (!canvases.length) return;
                const spreadHeight = Math.max(...canvases.map((canvas) => canvas.getBoundingClientRect().height));
                pageNumbers.forEach((pageNumber) => {
                    pageWrappers[pageNumber].style.height = `${spreadHeight}px`;
                    const canvas = pageWrappers[pageNumber].querySelector("canvas");
                    if (canvas) {
                        canvas.style.height = `${spreadHeight}px`;
                        canvas.style.width = "auto";
                    }
                });
            };

            const initialSpread = Math.max(0, Math.min(savedPage - 1, lastSpreadStart));
            await renderPage(initialSpread + 1);
            if (initialSpread + 2 <= pageLimit) await renderPage(initialSpread + 2);
            alignSpread(initialSpread);
            track.classList.add("is-ready");
            updatePage(initialSpread + 1);

            for (let pageNumber = 1; pageNumber <= pageLimit; pageNumber += 1) {
                if (renderedPages.has(pageNumber)) continue;
                renderPage(pageNumber).then(() => {
                    alignSpread(Math.floor((pageNumber - 1) / 2) * 2);
                }).catch(() => {});
            }
        }).catch(() => {
            previewPages.innerHTML = '<div class="preview-error">Buku tidak dapat dimuat. Silakan coba lagi.</div>';
        });
    </script>
</body>

</html>
