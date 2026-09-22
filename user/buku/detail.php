<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../config/app.php";

$id = (int)($_GET["id"] ?? 0);

ensure_rating_table($koneksi);
ensure_activity_logs($koneksi);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["rating"]) && !empty($_SESSION["id_user"])) {
    $id = (int)($_POST["id_buku"] ?? $id);
    $idUser = (int) $_SESSION["id_user"];
    $ratingValue = max(1, min(5, (int) $_POST["rating"]));
    $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_buku_rating (id_buku, id_user, rating) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), updated_at = CURRENT_TIMESTAMP");
    mysqli_stmt_bind_param($stmt, "iii", $id, $idUser, $ratingValue);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: " . BASE_URL . "/user/buku/detail.php?id=" . (int)$id);
    exit;
}
$stmt = mysqli_prepare($koneksi, "SELECT b.*,k.nama_kategori FROM tbl_buku b LEFT JOIN tbl_kategori k ON k.id_kategori=b.id_kategori WHERE id_buku=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$b) exit("Buku tidak ditemukan");
// cek status premium user
$isPremium = false;
$idUser = (int)($_SESSION["id_user"] ?? 0);
if ($idUser) {
    $stmt = mysqli_prepare($koneksi, "SELECT is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=?");
    mysqli_stmt_bind_param($stmt, "i", $idUser);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($user && $user["is_premium"]) {
        $today = new DateTime("today");
        $expiryDate = new DateTime($user["tanggal_premium_hingga"]);
        $isPremium = $today <= $expiryDate;
    }
}

$stmt = mysqli_prepare($koneksi, "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_rating FROM tbl_buku_rating WHERE id_buku=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$ratingSummary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$userRating = 0;
if ($idUser) {
    $stmt = mysqli_prepare($koneksi, "SELECT rating FROM tbl_buku_rating WHERE id_buku=? AND id_user=?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $idUser);
    mysqli_stmt_execute($stmt);
    $userRatingRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $userRating = (int)($userRatingRow["rating"] ?? 0);
}

$readSummary = ["total_dibaca" => 0];
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total_dibaca FROM activity_logs WHERE id_buku=? AND aktivitas='baca'");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $readSummary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: $readSummary;
    mysqli_stmt_close($stmt);
}

$averageRating = (float)($ratingSummary["avg_rating"] ?? 0);
$totalRating = (int)($ratingSummary["total_rating"] ?? 0);
$totalDibaca = (int)($readSummary["total_dibaca"] ?? 0);

$pdfPath = !empty($b["pdf_buku"]) ? PDF_DIR . $b["pdf_buku"] : "";
$pdfPageCount = !empty($pdfPath) && file_exists($pdfPath) ? get_pdf_page_count($pdfPath) : 0;
$pdfStatusText = $pdfPageCount > 0 ? (string) $pdfPageCount : "-";
?>
<!doctype html>
<html lang="id" class="detail-document">

<head>
    <meta charset="utf-8">
    <title><?= e($b["judul_buku"]) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/notification.css">
    <style>
        .detail-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 15px;
        }
        
        .detail-hero {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 40px;
            margin-bottom: 40px;
            align-items: start;
        }
        
        .detail-cover {
            position: sticky;
            top: 20px;
        }

        .detail-cover-frame { width: min(100%, 300px); }
        
        .detail-cover img {
            width: 100%;
            max-width: 300px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            display: block;
        }
        
        .detail-cover-fallback {
            width: 100%;
            max-width: 300px;
            aspect-ratio: 3/4;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4em;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .detail-info h1 {
            font-size: 2em;
            margin: 0 0 10px 0;
            color: #333;
            line-height: 1.3;
        }
        
        .detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            font-size: 0.85em;
            color: #999;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .meta-value {
            font-size: 1.1em;
            color: #333;
            font-weight: 500;
        }
        
        .detail-description {
            margin-bottom: 30px;
        }
        
        .detail-description h3 {
            font-size: 1.1em;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
        }
        
        .detail-description p {
            line-height: 1.8;
            color: #555;
            text-align: justify;
        }
        
        .detail-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 30px;
        }
        
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-read {
            background: #176b69;
            box-shadow: 0 5px 14px rgba(23, 107, 105, .16);
        }

        .btn-read:hover {
            background: #125654;
            box-shadow: 0 8px 20px rgba(23, 107, 105, .25);
        }

        .btn-preview {
            background: #d98b32;
        }

        .btn-preview:hover {
            background: #bd7322;
            box-shadow: 0 8px 20px rgba(217, 139, 50, 0.28);
        }
        
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95em;
            transition: all 0.3s ease;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background: #e8e8e8;
            border-color: #999;
        }

        .btn-upgrade {
            gap: 8px;
            background: #edf7f5;
            border-color: #b9cfcc;
            color: #176b69;
        }

        .btn-upgrade:hover {
            background: #dcefeb;
            border-color: #176b69;
            color: #125654;
        }

        .btn-upgrade__crown {
            display: inline-grid;
            flex: 0 0 24px;
            width: 24px;
            height: 24px;
            aspect-ratio: 1;
            place-items: center;
            border-radius: 50%;
            background: #e2ae32;
            color: #fff !important;
            box-shadow: 0 2px 6px rgba(180, 130, 24, .25);
        }

        .btn-upgrade__crown i {
            color: #fff !important;
            font-size: .72rem;
        }

        
        .premium-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .additional-info {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 26px;
            max-width: 570px;
        }
        
        .info-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: 110px;
            padding: 18px 14px;
            background: #ffffff;
            border: 1px solid #e8ecef;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }
        
        .info-card-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #667085;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .info-card-value {
            font-size: clamp(1.2rem, 2vw, 1.8rem);
            font-weight: 800;
            line-height: 1.2;
            color: #1f2937;
            letter-spacing: -0.02em;
        }

        .info-card--rating {
            cursor: pointer;
        }

        .rating-modal[hidden] {
            display: none;
        }

        .rating-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(15, 23, 42, .42);
        }

        .rating-modal__panel {
            width: min(100%, 390px);
            padding: 28px;
            border: 1px solid #e8ecef;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 22px 60px rgba(15, 23, 42, .2);
            text-align: center;
        }

        .rating-modal__title {
            margin: 0 0 6px;
            color: #173042;
            font-size: 1.25rem;
        }

        .rating-modal__text {
            margin: 0 0 22px;
            color: #667085;
            font-size: .9rem;
        }

        .rating-modal__close {
            float: right;
            border: 0;
            background: transparent;
            color: #98a2b3;
            font-size: 1.3rem;
            line-height: 1;
            cursor: pointer;
        }

        .rating-modal__stars {
            display: flex;
            justify-content: center;
            gap: 2px;
            margin-bottom: 22px;
        }

        .rating-modal__stars input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .rating-modal__stars label {
            display: grid;
            width: 36px;
            height: 40px;
            place-items: center;
            color: #c4ced6;
            cursor: pointer;
            font-size: 1.8rem;
            transition: color .15s ease, transform .15s ease;
        }

        .rating-modal__stars label:hover,
        .rating-modal__stars label.is-selected {
            color: #d08b00;
            transform: scale(1.08);
        }

        .rating-modal__submit {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 11px 18px;
            background: #176b69;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .book-rating {
            margin-top: 24px;
            padding: 20px;
            border: 1px solid #e7ebf1;
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff, #f6faf9);
        }

        .book-rating__label {
            display: block;
            margin-bottom: 12px;
            font-weight: 700;
            color: #173042;
        }

        .book-rating__stars {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .book-rating__star {
            appearance: none;
            width: 42px;
            height: 42px;
            border: 1px solid #d4dfe6;
            border-radius: 50%;
            background: #fff;
            color: #c4ced6;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .book-rating__star:hover,
        .book-rating__star:checked {
            border-color: #e2ae32;
            background: #fff8db;
            color: #d08b00;
            box-shadow: 0 8px 18px rgba(226, 174, 50, 0.18);
        }

        .book-rating__submit {
            border: 0;
            border-radius: 10px;
            background: #176b69;
            color: white;
            padding: 10px 18px;
            font-weight: 700;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .detail-hero {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .detail-cover {
                position: static;
            }
            
            .detail-info h1 {
                font-size: 1.5em;
            }
            
            .detail-meta {
                gap: 15px;
            }
        }
    </style>
</head>

<body class="user-page detail-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    
    <main class="detail-container">
        <!-- Hero Section -->
        <div class="detail-hero">
            <!-- Left: Cover -->
            <div class="detail-cover">
                <div class="detail-cover-frame">
                    <?php if (!empty($b["cover_buku"])): ?>
                        <img src="<?= BASE_URL ?>/uploads/cover/<?= e($b["cover_buku"]) ?>" alt="Cover <?= e($b["judul_buku"]) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="detail-cover-fallback">📖</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right: Info -->
            <div class="detail-info">
                <h1><?= e($b["judul_buku"]) ?></h1>
                
                <!-- Meta Info -->
                <div class="detail-meta">
                    <div class="meta-item">
                        <span class="meta-label">Penulis</span>
                        <span class="meta-value"><?= e($b["penulis_buku"]) ?></span>
                    </div>
                    
                    <?php if (!empty($b["penerbit_buku"])): ?>
                        <div class="meta-item">
                            <span class="meta-label">Penerbit</span>
                            <span class="meta-value"><?= e($b["penerbit_buku"]) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($b["tahun_terbit"])): ?>
                        <div class="meta-item">
                            <span class="meta-label">Tahun Terbit</span>
                            <span class="meta-value"><?= $b["tahun_terbit"] ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="meta-item">
                        <span class="meta-label">Kategori</span>
                        <span class="meta-value"><?= e($b["nama_kategori"] ?? "Umum") ?></span>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="detail-description">
                    <h3>Deskripsi</h3>
                    <p><?= nl2br(e($b["deskripsi"] ?? "Tidak ada deskripsi.")) ?></p>
                </div>
                
                <!-- Action Buttons -->
                <div class="detail-actions">
                    <?php if ($isPremium): ?>
                        <a href="<?= BASE_URL ?>/user/buku/read.php?id=<?= $id ?>" class="btn-primary btn-read">
                            Baca Buku Lengkap
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/user/buku/read.php?id=<?= $id ?>" class="btn-primary btn-preview">
                            Lihat Preview
                        </a>
                        <a href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php" class="btn-secondary btn-upgrade">
                            <span class="btn-upgrade__crown"><i class="fa-solid fa-crown" aria-hidden="true"></i></span>
                            Upgrade - Rp 14.999
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>/user/buku/index.php" class="btn-secondary" style="margin-top: 5px;">
                        Kembali ke Katalog
                    </a>
                </div>
                
                <!-- Additional Info -->
                <?php if (!empty($b["deskripsi"]) || !empty($b["penerbit_buku"])): ?>
                    <div class="additional-info">
                        <div class="info-card">
                            <div class="info-card-label">📄 Halaman</div>
                            <div class="info-card-value"><?= e($pdfStatusText) ?></div>
                        </div>
                        <div class="info-card info-card--rating" data-rating-open role="button" tabindex="0" aria-label="Beri rating buku">
                            <div class="info-card-label">⭐ Rating</div>
                            <div class="info-card-value"><?= $totalRating > 0 ? number_format($averageRating, 1, ",", ".") : "-" ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-label">👥 Dibaca</div>
                            <div class="info-card-value"><?= number_format($totalDibaca, 0, ",", ".") ?>x</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . "/../partials/footer.php"; ?>

    <?php if ($idUser): ?>
        <div class="rating-modal" data-rating-modal hidden>
            <div class="rating-modal__panel" role="dialog" aria-modal="true" aria-labelledby="rating-modal-title">
                <button type="button" class="rating-modal__close" data-rating-close aria-label="Tutup">&times;</button>
                <h2 class="rating-modal__title" id="rating-modal-title">Beri Rating Buku</h2>
                <p class="rating-modal__text">Pilih penilaian untuk buku ini.</p>
                <form method="post">
                    <input type="hidden" name="id_buku" value="<?= (int)$id ?>">
                    <div class="rating-modal__stars">
                        <?php for ($star = 1; $star <= 5; $star++): ?>
                            <input id="rating-star-<?= $star ?>" type="radio" name="rating" value="<?= $star ?>" <?= $userRating === $star ? "checked" : "" ?> required>
                            <label for="rating-star-<?= $star ?>" aria-label="<?= $star ?> bintang">★</label>
                        <?php endfor; ?>
                    </div>
                    <button type="submit" class="rating-modal__submit">Simpan Rating</button>
                </form>
            </div>
        </div>
        <script>
            const ratingModal = document.querySelector("[data-rating-modal]");
            const ratingOpen = document.querySelector("[data-rating-open]");
            const ratingClose = document.querySelector("[data-rating-close]");
            const ratingInputs = document.querySelectorAll(".rating-modal__stars input");
            const ratingLabels = document.querySelectorAll(".rating-modal__stars label");

            const updateRatingStars = (value) => {
                ratingLabels.forEach((label, index) => {
                    label.classList.toggle("is-selected", index < value);
                });
            };

            ratingInputs.forEach((input) => {
                input.addEventListener("change", () => updateRatingStars(Number(input.value)));
                if (input.checked) updateRatingStars(Number(input.value));
            });

            const closeRatingModal = () => {
                ratingModal.hidden = true;
                document.body.style.overflow = "";
            };

            const openRatingModal = () => {
                ratingModal.hidden = false;
                document.body.style.overflow = "hidden";
            };

            ratingOpen?.addEventListener("click", openRatingModal);
            ratingOpen?.addEventListener("keydown", (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    openRatingModal();
                }
            });
            ratingClose?.addEventListener("click", closeRatingModal);
            ratingModal?.addEventListener("click", (event) => {
                if (event.target === ratingModal) closeRatingModal();
            });
            document.addEventListener("keydown", (event) => {
                if (event.key === "Escape" && !ratingModal.hidden) closeRatingModal();
            });
        </script>
    <?php endif; ?>
</body>

</html>