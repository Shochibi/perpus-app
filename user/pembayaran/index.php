<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";

$idUser = (int)$_SESSION["id_user"];

// ambil info user
$stmt = mysqli_prepare($koneksi, "SELECT fullname, is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=?");
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$isPremium = false;
$daysLeft = 0;
$premiumExpiry = null;

if ($user && $user["is_premium"]) {
    $today = new DateTime("today");
    $expiryDate = new DateTime($user["tanggal_premium_hingga"]);
    if ($today <= $expiryDate) {
        $isPremium = true;
        $premiumExpiry = $user["tanggal_premium_hingga"];
        $interval = $today->diff($expiryDate);
        $daysLeft = (int)$interval->format("%r%a");
    }
}

// cek apakah ada pembayaran pending
$hasPendingPayment = false;
$pendingPayment = null;
$pendingPaymentsResult = mysqli_query($koneksi, "SELECT * FROM tbl_pembayaran WHERE id_user=$idUser AND status='pending' ORDER BY tanggal_dibuat DESC LIMIT 1");
if ($pendingPaymentsResult && mysqli_num_rows($pendingPaymentsResult) > 0) {
    $hasPendingPayment = true;
    $pendingPayment = mysqli_fetch_assoc($pendingPaymentsResult);
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Status Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
    <style>
        .status-container { max-width: 900px; margin: 28px auto 44px; padding: 0 18px; }
        .status-container > h1 { margin: 0 0 22px; color: #173042; font-size: clamp(1.7rem, 3vw, 2.3rem); }
        .status-card { padding: 18px 20px; border-radius: 9px; margin-bottom: 18px; }
        .status-active { background: #e3f1ec; border: 1px solid #b5d8cb; color: #195b4e; }
        .status-inactive { background: #f9eceb; border: 1px solid #e8c3c0; color: #7d3733; }
        .status-title { font-size: 1.25em; margin-bottom: 8px; font-weight: 700; }
        .status-container .card { padding: 20px; margin-bottom: 18px; }
        .status-container .card h2 { margin-top: 0; color: #173042; font-size: 1.25rem; }
        .status-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
        .status-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 16px;
            border-radius: 7px;
            color: #fff;
            background: #176b69;
            font-weight: 700;
            text-decoration: none;
            transition: background-color .2s ease, transform .2s ease, box-shadow .2s ease;
        }
        .status-button:hover {
            color: #fff;
            background: #125654;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(23, 107, 105, .2);
        }
        .status-button.is-muted { color: #52636c; background: #edf2f1; border: 1px solid #d5e0de; }
        .status-button.is-muted:hover { color: #173042; background: #e2ebea; box-shadow: none; }
        @media (max-width: 650px) {
            .status-container { margin-top: 22px; padding: 0 14px; }
            .status-container .card { padding: 16px; }
        }
    </style>
</head>

<body class="user-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    
    <main class="status-container">
        <!-- Pending Payment Alert -->
        <?php if ($hasPendingPayment): ?>
            <div class="status-card" style="background: #fff3cd; border: 2px solid #ffc107; color: #856404;">
                <div class="status-title" style="color: #856404;">
                    Menunggu Persetujuan Pembayaran
                </div>
                <p><strong>Pembayaran Anda sedang diproses oleh admin.</strong></p>
                <div style="background: white; padding: 12px; border-radius: 5px; margin: 10px 0;">
                    <p style="margin: 5px 0;"><strong>Nominal:</strong> Rp <?= number_format($pendingPayment['nominal']) ?></p>
                    <p style="margin: 5px 0;"><strong>Metode:</strong> <?= ucfirst(str_replace('_', ' ', $pendingPayment['metode_pembayaran'])) ?></p>
                    <p style="margin: 5px 0;"><strong>Tanggal Pengajuan:</strong> <?= date('d M Y H:i', strtotime($pendingPayment['tanggal_dibuat'])) ?></p>
                </div>
                <div style="background: #e7f3ff; border-left: 4px solid #0066cc; padding: 10px; border-radius: 3px; margin: 10px 0;">
                    <p style="margin: 5px 0; font-size: 0.95em;"><strong>Langkah Selanjutnya:</strong></p>
                    <ol style="margin: 5px 0; padding-left: 20px; font-size: 0.95em;">
                        <li>Transfer ke nomor E-Wallet admin: <strong><?= ADMIN_EWALLET ?></strong></li>
                        <li>Kirim bukti transfer ke admin (jika diminta)</li>
                        <li>Admin akan approve dalam 5-15 menit setelah pembayaran diterima</li>
                    </ol>
                </div>
                <p style="margin: 10px 0; font-size: 0.9em;"><small>Refresh halaman ini untuk melihat status terbaru</small></p>
            </div>
        <?php endif; ?>
        
        <!-- Status Card -->
        <div class="status-card <?= $isPremium ? 'status-active' : 'status-inactive' ?>">
            <?php if ($isPremium): ?>
                <p>Sisa waktu: <strong><?= $daysLeft ?> hari lagi</strong></p>
                <div class="status-actions">
                    <a href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php" class="status-button">Perpanjang Premium</a>
                </div>
            <?php else: ?>
                <p>Akun Anda saat ini menggunakan Free Account. Upgrade ke Premium untuk akses penuh semua buku.</p>
                <div class="status-actions">
                    <a href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php" class="status-button">Upgrade Premium mulai dari Rp 14.999</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Features -->
        <div class="card">
            <h2>Fitur Premium</h2>
            <ul style="padding-left: 20px; line-height: 2;">
                <li>✓ Baca semua buku tanpa batasan halaman</li>
                <li>✓ Akses 1000+ buku dalam koleksi</li>
                <li>✓ Download PDF untuk dibaca offline</li>
                <li>✓ Bookmark dan catatan pribadi</li>
                <li>✓ Rekomendasi buku personal</li>
                <li>✓ Dukungan pelanggan prioritas</li>
            </ul>
        </div>
        
        <div class="status-actions" style="justify-content: center; margin-top: 24px;">
            <a href="<?= BASE_URL ?>/user/buku/index.php" class="status-button is-muted">Kembali ke Katalog</a>
        </div>
    </main>
    
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html>
