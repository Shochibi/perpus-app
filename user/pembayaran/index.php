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

// ambil riwayat pembayaran
$stmt = mysqli_prepare($koneksi, "SELECT * FROM tbl_pembayaran WHERE id_user=? ORDER BY tanggal_dibuat DESC");
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$payments = mysqli_stmt_get_result($stmt);

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
        .status-container { max-width: 800px; margin: 40px auto; }
        .status-card { padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .status-active { background: #d4edda; border: 2px solid #28a745; color: #155724; }
        .status-inactive { background: #f8d7da; border: 2px solid #dc3545; color: #721c24; }
        .status-title { font-size: 1.5em; margin-bottom: 10px; }
        .expiry-date { font-size: 1.2em; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #f8f9fa; font-weight: bold; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 0.9em; }
        .badge-berhasil { background-color: #d4edda; color: #155724; }
        .badge-pending { background-color: #fff3cd; color: #856404; }
        .badge-gagal { background-color: #f8d7da; color: #721c24; }
    </style>
</head>

<body class="user-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    
    <main class="status-container">
        <h1>💳 Status Premium Membership</h1>
        
        <!-- Pending Payment Alert -->
        <?php if ($hasPendingPayment): ?>
            <div class="status-card" style="background: #fff3cd; border: 2px solid #ffc107; color: #856404;">
                <div class="status-title" style="color: #856404;">
                    ⏳ Menunggu Persetujuan Pembayaran
                </div>
                <p><strong>Pembayaran Anda sedang diproses oleh admin.</strong></p>
                <div style="background: white; padding: 12px; border-radius: 5px; margin: 10px 0;">
                    <p style="margin: 5px 0;"><strong>Nominal:</strong> Rp <?= number_format($pendingPayment['nominal']) ?></p>
                    <p style="margin: 5px 0;"><strong>Metode:</strong> <?= ucfirst(str_replace('_', ' ', $pendingPayment['metode_pembayaran'])) ?></p>
                    <p style="margin: 5px 0;"><strong>Tanggal Pengajuan:</strong> <?= date('d M Y H:i', strtotime($pendingPayment['tanggal_dibuat'])) ?></p>
                </div>
                <div style="background: #e7f3ff; border-left: 4px solid #0066cc; padding: 10px; border-radius: 3px; margin: 10px 0;">
                    <p style="margin: 5px 0; font-size: 0.95em;"><strong>📱 Langkah Selanjutnya:</strong></p>
                    <ol style="margin: 5px 0; padding-left: 20px; font-size: 0.95em;">
                        <li>Transfer ke nomor E-Wallet admin: <strong><?= ADMIN_EWALLET ?></strong></li>
                        <li>Kirim bukti transfer ke admin (jika diminta)</li>
                        <li>Admin akan approve dalam 5-15 menit setelah pembayaran diterima</li>
                    </ol>
                </div>
                <p style="margin: 10px 0; font-size: 0.9em;"><small>💡 Refresh halaman ini untuk melihat status terbaru</small></p>
            </div>
        <?php endif; ?>
        
        <!-- Status Card -->
        <div class="status-card <?= $isPremium ? 'status-active' : 'status-inactive' ?>">
            <div class="status-title">
                <?= $isPremium ? '✓ Premium Aktif' : '✗ Free Account' ?>
            </div>
            
            <?php if ($isPremium): ?>
                <div class="expiry-date">
                    Berlaku hingga: <strong><?= date('d MMMM Y', strtotime($premiumExpiry)) ?></strong>
                </div>
                <p>Sisa waktu: <strong><?= $daysLeft ?> hari lagi</strong></p>
                <a href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php" class="button">Perpanjang Premium</a>
            <?php else: ?>
                <p>Akun Anda saat ini menggunakan Free Account. Upgrade ke Premium untuk akses penuh semua buku.</p>
                <a href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php" class="button">Upgrade ke Premium Sekarang</a>
            <?php endif; ?>
        </div>
        
        <!-- Payment History -->
        <div class="card">
            <h2>📋 Riwayat Pembayaran</h2>
            
            <?php if (mysqli_num_rows($payments) === 0): ?>
                <p style="text-align: center; color: #666;">Belum ada riwayat pembayaran.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Nominal</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($payment = mysqli_fetch_assoc($payments)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d M Y H:i', strtotime($payment['tanggal_dibuat'])) ?></td>
                                <td>Rp <?= number_format($payment['nominal']) ?></td>
                                <td><?= $payment['durasi_hari'] ?> hari</td>
                                <td>
                                    <span class="status-badge badge-<?= $payment['status'] ?>">
                                        <?= ucfirst($payment['status']) ?>
                                    </span>
                                </td>
                                <td><?= ucfirst(str_replace('_', ' ', $payment['metode_pembayaran'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Features -->
        <div class="card">
            <h2>✨ Fitur Premium</h2>
            <ul style="padding-left: 20px; line-height: 2;">
                <li>✓ Baca semua buku tanpa batasan halaman</li>
                <li>✓ Akses 1000+ buku dalam koleksi</li>
                <li>✓ Download PDF untuk dibaca offline</li>
                <li>✓ Bookmark dan catatan pribadi</li>
                <li>✓ Rekomendasi buku personal</li>
                <li>✓ Dukungan pelanggan prioritas</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?= BASE_URL ?>/user/buku/index.php" class="button">← Kembali ke Katalog</a>
        </div>
    </main>
    
    <?php include __DIR__ . "/../partials/footer.php"; ?>
</body>

</html>
