<?php
require_once __DIR__ . "/../../middleware/admin.php";
require_once __DIR__ . "/../../config/koneksi.php";

// proses aksi
if (isset($_GET["approve"])) {
    $id = (int)$_GET["approve"];
    mysqli_begin_transaction($koneksi);
    try {
        // ambil data pembayaran
        $stmt = mysqli_prepare($koneksi, "SELECT id_user, durasi_hari FROM tbl_pembayaran WHERE id_pembayaran=? AND status='pending' FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $payment = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if (!$payment) throw new Exception("Pembayaran tidak ditemukan atau sudah diproses");
        
        // update status pembayaran
        $status = "berhasil";
        $stmt = mysqli_prepare($koneksi, "UPDATE tbl_pembayaran SET status=?, tanggal_pembayaran=NOW() WHERE id_pembayaran=?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // hitung tanggal premium hangga
        $stmt = mysqli_prepare($koneksi, "SELECT is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=?");
        mysqli_stmt_bind_param($stmt, "i", $payment["id_user"]);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if ($user["is_premium"] && new DateTime($user["tanggal_premium_hingga"]) > new DateTime("today")) {
            $expiryDate = new DateTime($user["tanggal_premium_hingga"]);
        } else {
            $expiryDate = new DateTime("today");
        }
        $expiryDate->add(new DateInterval("P" . $payment["durasi_hari"] . "D"));
        $newExpiry = $expiryDate->format("Y-m-d");
        
        // update user premium
        $stmt = mysqli_prepare($koneksi, "UPDATE tbl_user SET is_premium=1, tanggal_premium_hingga=? WHERE id_user=?");
        mysqli_stmt_bind_param($stmt, "si", $newExpiry, $payment["id_user"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        log_activity($koneksi, (int)$_SESSION["id_user"], null, "pembayaran_approve", "Menyetujui pembayaran #" . $id);
        
        mysqli_commit($koneksi);
        flash("Pembayaran berhasil disetujui.");
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        flash($e->getMessage(), "error");
    }
    redirect("admin/pembayaran/index.php");
}

if (isset($_GET["reject"])) {
    $id = (int)$_GET["reject"];
    $status = "gagal";
    $stmt = mysqli_prepare($koneksi, "UPDATE tbl_pembayaran SET status=? WHERE id_pembayaran=? AND status='pending'");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    if ($affected > 0) {
        log_activity($koneksi, (int)$_SESSION["id_user"], null, "pembayaran_reject", "Menolak pembayaran #" . $id);
        flash("Pembayaran ditolak.");
    } else {
        flash("Pembayaran tidak ditemukan atau sudah diproses", "error");
    }
    redirect("admin/pembayaran/index.php");
}

// ambil daftar pembayaran
$filter = $_GET["filter"] ?? "semua";
$query = "SELECT p.*, u.fullname, u.username FROM tbl_pembayaran p JOIN tbl_user u ON u.id_user=p.id_user";

if ($filter === "pending") {
    $query .= " WHERE p.status='pending'";
} elseif ($filter === "berhasil") {
    $query .= " WHERE p.status='berhasil'";
} elseif ($filter === "gagal") {
    $query .= " WHERE p.status='gagal'";
}

$query .= " ORDER BY p.tanggal_dibuat DESC";
$payments = mysqli_query($koneksi, $query);

// hitung statistik
$stats = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT 
    COUNT(*) total,
    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
    SUM(CASE WHEN status='berhasil' THEN 1 ELSE 0 END) berhasil,
    SUM(CASE WHEN status='gagal' THEN 1 ELSE 0 END) gagal,
    SUM(CASE WHEN status='berhasil' THEN nominal ELSE 0 END) total_revenue
FROM tbl_pembayaran"));
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Manajemen Pembayaran</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <style>
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
        .stat-box h3 { margin: 0; color: #666; font-size: 0.9em; }
        .stat-box .value { font-size: 1.8em; font-weight: bold; margin: 5px 0 0 0; }
        .filters { margin-bottom: 20px; }
        .filters a { display: inline-block; padding: 8px 15px; background: #f8f9fa; border: 1px solid #ddd; margin-right: 10px; text-decoration: none; color: #333; border-radius: 3px; }
        .filters a.active { background: #007bff; color: white; border-color: #007bff; }
        table { width: 100%; border-collapse: collapse; }
        table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: bold; border-bottom: 2px solid #ddd; }
        table td { padding: 12px; border-bottom: 1px solid #ddd; }
        table tr:hover { background: #f8f9fa; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 0.85em; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-berhasil { background: #d4edda; color: #155724; }
        .badge-gagal { background: #f8d7da; color: #721c24; }
        .action-buttons { display: flex; gap: 5px; }
        .action-buttons a { padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 0.9em; cursor: pointer; }
        .btn-approve { background: #28a745; color: white; }
        .btn-approve:hover { background: #218838; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-reject:hover { background: #c82333; }
        .btn-view { background: #007bff; color: white; }
    </style>
</head>

<body><?php render_flash(); ?><?php include __DIR__ . "/../partials/sidebar.php"; ?><main class="main">
        <h1>💳 Manajemen Pembayaran Premium</h1>
        
        <!-- Info Pembayaran E-Wallet -->
        <div class="card" style="background: #e7f3ff; border: 1px solid #b3d9ff; margin-bottom: 20px;">
            <h3 style="margin-top: 0; color: #004085;">📱 Informasi Penerimaan Pembayaran E-Wallet</h3>
            <p style="color: #004085; margin: 5px 0;"><strong>Nomor E-Wallet Admin untuk Menerima Pembayaran:</strong></p>
            <div style="background: white; padding: 12px; border-radius: 5px; border-left: 4px solid #0066cc; margin: 10px 0;">
                <p style="margin: 0; font-size: 1.1em;"><strong><?= ADMIN_EWALLET ?></strong></p>
                <p style="margin: 5px 0; font-size: 0.9em; color: #666;"><small>Jika pembayaran masuk ke nomor ini, approve pembayaran user</small></p>
            </div>
            <p style="color: #004085; margin: 10px 0 0 0;"><strong>💡 Tips:</strong> Ketika user melakukan transfer, mereka akan mencantumkan nama mereka. Cocokkan dengan nama user di tabel pembayaran sebelum approve.</p>
        </div>
        
        <!-- Statistik -->
        <div class="stats">
            <div class="stat-box">
                <h3>Total Transaksi</h3>
                <div class="value"><?= $stats["total"] ?></div>
            </div>
            <div class="stat-box" style="border-left-color: #ffc107;">
                <h3>Menunggu Persetujuan</h3>
                <div class="value"><?= $stats["pending"] ?></div>
            </div>
            <div class="stat-box" style="border-left-color: #28a745;">
                <h3>Berhasil</h3>
                <div class="value"><?= $stats["berhasil"] ?></div>
            </div>
            <div class="stat-box" style="border-left-color: #dc3545;">
                <h3>Ditolak</h3>
                <div class="value"><?= $stats["gagal"] ?></div>
            </div>
            <div class="stat-box" style="border-left-color: #17a2b8;">
                <h3>Total Revenue</h3>
                <div class="value">Rp <?= number_format($stats["total_revenue"] ?? 0) ?></div>
            </div>
        </div>
        
        <!-- Filter -->
        <div class="filters card">
            <a href="?filter=semua" class="<?= $filter === 'semua' ? 'active' : '' ?>">Semua (<?= $stats["total"] ?>)</a>
            <a href="?filter=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pending (<?= $stats["pending"] ?>)</a>
            <a href="?filter=berhasil" class="<?= $filter === 'berhasil' ? 'active' : '' ?>">Berhasil (<?= $stats["berhasil"] ?>)</a>
            <a href="?filter=gagal" class="<?= $filter === 'gagal' ? 'active' : '' ?>">Ditolak (<?= $stats["gagal"] ?>)</a>
        </div>
        
        <!-- Tabel Pembayaran -->
        <div class="card">
            <h2>Daftar Pembayaran</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Nominal</th>
                        <th>Durasi</th>
                        <th>Tanggal</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($p = mysqli_fetch_assoc($payments)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <strong><?= e($p["fullname"]) ?></strong><br>
                                <small style="color: #666;">@<?= e($p["username"]) ?></small>
                            </td>
                            <td>Rp <?= number_format($p["nominal"]) ?></td>
                            <td><?= $p["durasi_hari"] ?> hari</td>
                            <td><?= date("d M Y H:i", strtotime($p["tanggal_dibuat"])) ?></td>
                            <td><?= ucfirst(str_replace("_", " ", $p["metode_pembayaran"])) ?></td>
                            <td>
                                <span class="status-badge badge-<?= $p["status"] ?>">
                                    <?= ucfirst($p["status"]) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($p["status"] === "pending"): ?>
                                        <a href="?approve=<?= $p["id_pembayaran"] ?>" class="btn-approve" onclick="return confirm('Setujui pembayaran ini?')">Setujui</a>
                                        <a href="?reject=<?= $p["id_pembayaran"] ?>" class="btn-reject" onclick="return confirm('Tolak pembayaran ini?')">Tolak</a>
                                    <?php else: ?>
                                        <span style="color: #999;">Selesai</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>
