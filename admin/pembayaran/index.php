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
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
</head>

<body><?php render_flash(); ?><?php include __DIR__ . "/../partials/sidebar.php"; ?><main class="main">
        <div class="admin-page-heading">
            <div>
                <span class="admin-kicker">LAYANAN PREMIUM</span>
                <h1>Manajemen pembayaran</h1>
                <p>Periksa transaksi dan kelola akses premium anggota.</p>
            </div>
        </div>

        <div class="card payment-notice">
            <div>
                <span class="admin-kicker">SALURAN PEMBAYARAN</span>
                <h2>E-Wallet penerimaan</h2>
                <p>Cocokkan nama pengirim dengan data anggota sebelum menyetujui transaksi.</p>
            </div>
            <strong class="payment-notice__number"><?= e(ADMIN_EWALLET) ?></strong>
        </div>

        <div class="stats payment-stats">
            <div class="stat-box payment-stat">
                <span>Total transaksi</span><strong><?= $stats["total"] ?></strong>
            </div>
            <div class="stat-box payment-stat payment-stat--pending">
                <span>Menunggu persetujuan</span><strong><?= $stats["pending"] ?></strong>
            </div>
            <div class="stat-box payment-stat payment-stat--success">
                <span>Berhasil</span><strong><?= $stats["berhasil"] ?></strong>
            </div>
            <div class="stat-box payment-stat payment-stat--failed">
                <span>Ditolak</span><strong><?= $stats["gagal"] ?></strong>
            </div>
            <div class="stat-box payment-stat payment-stat--revenue">
                <span>Total revenue</span><strong>Rp <?= number_format($stats["total_revenue"] ?? 0) ?></strong>
            </div>
        </div>

        <div class="filters card payment-filters">
            <span class="payment-filters__label">Tampilkan</span>
            <a href="?filter=semua" class="<?= $filter === 'semua' ? 'active' : '' ?>">Semua (<?= $stats["total"] ?>)</a>
            <a href="?filter=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pending (<?= $stats["pending"] ?>)</a>
            <a href="?filter=berhasil" class="<?= $filter === 'berhasil' ? 'active' : '' ?>">Berhasil (<?= $stats["berhasil"] ?>)</a>
            <a href="?filter=gagal" class="<?= $filter === 'gagal' ? 'active' : '' ?>">Ditolak (<?= $stats["gagal"] ?>)</a>
        </div>

        <div class="card payment-table-card">
            <div class="payment-table-heading">
                <h2>Daftar pembayaran</h2><span><?= $stats["total"] ?> transaksi</span>
            </div>
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
                    <?php $no = 1;
                    while ($p = mysqli_fetch_assoc($payments)): ?>
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
                                        <span class="payment-complete">Selesai</span>
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