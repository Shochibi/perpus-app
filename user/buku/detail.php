<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
$id = (int)($_GET["id"] ?? 0);
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
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .info-card {
            text-align: center;
        }
        
        .info-card-label {
            font-size: 0.85em;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .info-card-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
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
                <?php if (!empty($b["cover_buku"])): ?>
                    <img src="<?= BASE_URL ?>/uploads/cover/<?= e($b["cover_buku"]) ?>" alt="Cover <?= e($b["judul_buku"]) ?>" loading="lazy">
                <?php else: ?>
                    <div class="detail-cover-fallback">📖</div>
                <?php endif; ?>
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
                        <a href="<?= BASE_URL ?>/user/buku/read.php?id=<?= $id ?>" class="btn-primary">
                            Baca Buku Lengkap
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/user/buku/read.php?id=<?= $id ?>" class="btn-primary">
                            Lihat Preview (10 Halaman)
                        </a>
                        <a href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php" class="btn-secondary">
                            Upgrade ke Premium - Rp <?= number_format(PREMIUM_PRICE) ?>
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
                            <div class="info-card-value">-</div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-label">⭐ Rating</div>
                            <div class="info-card-value">-</div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-label">👥 Dibaca</div>
                            <div class="info-card-value">-</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html>