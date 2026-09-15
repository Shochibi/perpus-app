<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";

$idUser = (int)$_SESSION["id_user"];
$nominal = PREMIUM_PRICE;   // Gunakan konstanta dari config
$durasi = PREMIUM_DURATION; // Gunakan konstanta dari config

// cek status premium saat ini
$stmt = mysqli_prepare($koneksi, "SELECT is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=?");
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$isPremium = false;
$premiumExpiry = null;
if ($user && $user["is_premium"]) {
    $today = new DateTime("today");
    $expiryDate = new DateTime($user["tanggal_premium_hingga"]);
    if ($today <= $expiryDate) {
        $isPremium = true;
        $premiumExpiry = $user["tanggal_premium_hingga"];
    }
}

// proses pembayaran
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["proses_pembayaran"])) {
    mysqli_begin_transaction($koneksi);
    try {
        // buat record pembayaran dengan status PENDING
        $metode = PAYMENT_METHOD;
        $status = "pending";
        $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_pembayaran (id_user, nominal, metode_pembayaran, status, durasi_hari) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissi", $idUser, $nominal, $metode, $status, $durasi);
        mysqli_stmt_execute($stmt);
        $idPembayaran = mysqli_insert_id($koneksi);
        mysqli_stmt_close($stmt);
        
        log_activity($koneksi, $idUser, null, "pembayaran_pending", "Inisiasi pembayaran premium - Rp " . number_format($nominal) . " (menunggu konfirmasi)");
        
        mysqli_commit($koneksi);
        flash("Permintaan pembayaran Anda telah dibuat. Silakan transfer sesuai instruksi dan tunggu approval admin.", "success");
        redirect("user/pembayaran/index.php");
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        flash("Gagal memproses pembayaran: " . $e->getMessage(), "error");
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Beli Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
    <style>
        .premium-container { max-width: 600px; margin: 40px auto; }
        .premium-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 20px; }
        .premium-card h2 { margin-top: 0; }
        .features { list-style: none; padding: 0; }
        .features li { padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .features li:before { content: "✓ "; margin-right: 10px; font-weight: bold; }
        .price { font-size: 2em; margin: 20px 0; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>

<body class="user-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    
    <main class="premium-container">
        <h1>💎 Premium Membership</h1>
        
        <?php if ($isPremium): ?>
            <div class="info-box" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
                <strong>✓ Akun Anda Sudah Premium</strong><br>
                Premium hingga: <strong><?= date('d M Y', strtotime($premiumExpiry)) ?></strong><br>
                <a href="<?= BASE_URL ?>/user/pembayaran/index.php">Lihat Riwayat Pembayaran</a>
            </div>
        <?php endif; ?>
        
        <div class="premium-card">
            <h2>Akses Tanpa Batas</h2>
            <ul class="features">
                <li>Baca semua buku tanpa batasan halaman</li>
                <li>Akses 1000+ buku dalam koleksi</li>
                <li>Donwload PDF untuk dibaca offline</li>
                <li>Dukungan pelanggan prioritas</li>
                <li>Akses selamanya selama aktif</li>
            </ul>
            <div class="price">Rp <?= number_format($nominal) ?></div>
            <small>Durasi: <?= $durasi ?> hari</small>
        </div>
        
        <form method="POST" class="card">
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">Informasi Pembayaran</h3>
                <p><strong>Harga:</strong> Rp <?= number_format($nominal) ?></p>
                <p><strong>Durasi:</strong> <?= $durasi ?> hari</p>
                <p><strong>Metode:</strong> 📱 E-Wallet</p>
                <hr>
                <p><strong>Total yang harus dibayar:</strong> <span style="font-size: 1.3em; color: #667eea; font-weight: bold;">Rp <?= number_format($nominal) ?></span></p>
            </div>
            
            <div class="info-box" style="background: #e7f3ff; border: 1px solid #b3d9ff; color: #004085;">
                <strong>📱 Metode Pembayaran E-Wallet</strong>
                <p style="margin: 10px 0; font-size: 0.95em;">Anda dapat melakukan pembayaran melalui e-wallet berikut:</p>
                <div style="background: white; padding: 12px; border-radius: 5px; border-left: 4px solid #667eea; margin: 10px 0;">
                    <p style="margin: 5px 0;"><strong>💳 GCash / PayMaya / OVO / Dana / Linkaja</strong></p>
                    <p style="margin: 5px 0; color: #666; font-size: 0.9em;">Nomor E-Wallet Admin: <strong><?= ADMIN_EWALLET ?></strong></p>
                </div>
            </div>
            
            <div class="info-box">
                <strong>📋 Langkah-Langkah Pembayaran:</strong>
                <ol style="padding-left: 20px; margin: 10px 0; line-height: 1.8;">
                    <li><strong>Buka aplikasi e-wallet Anda</strong> (GCash, PayMaya, OVO, Dana, atau Linkaja)</li>
                    <li><strong>Transfer ke nomor e-wallet admin</strong>: <?= ADMIN_EWALLET ?></li>
                    <li><strong>Masukkan nominal</strong>: Rp <?= number_format($nominal) ?></li>
                    <li><strong>Cantumkan catatan/referensi</strong>: "Premium <?= e($_SESSION['fullname']) ?>"</li>
                    <li><strong>Klik tombol "Bayar Sekarang"</strong> di bawah untuk mendaftarkan pembayaran Anda</li>
                    <li><strong>Admin akan verifikasi</strong> dan mengaktifkan akun premium Anda dalam 5-15 menit</li>
                </ol>
            </div>
            
            <button type="submit" name="proses_pembayaran" class="button" style="width: 100%; padding: 15px; font-size: 1.1em; cursor: pointer; background-color: #667eea; margin-bottom: 10px;">
                ✓ Bayar Sekarang - Rp <?= number_format($nominal) ?>
            </button>
            
            <div style="text-align: center; margin-top: 10px;">
                <p style="font-size: 0.9em; color: #666;">Setelah klik "Bayar Sekarang", silakan lakukan transfer e-wallet Anda.<br>Admin akan approve dalam beberapa menit setelah pembayaran diterima.</p>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="<?= BASE_URL ?>/user/buku/index.php" class="button" style="background-color: #999;">← Kembali ke Katalog</a>
            </div>
        </form>
        
        <div class="info-box" style="background: #e7f3ff; border: 1px solid #b3d9ff; color: #004085; margin-top: 30px;">
            <strong>❓ Pertanyaan?</strong><br>
            Hubungi admin untuk bantuan pembayaran atau aktivasi akun.
        </div>
    </main>
    
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html>
