<?php
require_once __DIR__ . "/../../middleware/admin.php";
require_once __DIR__ . "/../../config/koneksi.php";
if (isset($_GET["kembali"])) {
    $id = (int)$_GET["kembali"];
    mysqli_begin_transaction($koneksi);
    try {
        $stmt = mysqli_prepare($koneksi, "SELECT status FROM tbl_peminjaman WHERE id_peminjaman=? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if (!$p || $p["status"] !== "dipinjam") throw new Exception();
        $stmt = mysqli_prepare($koneksi, "SELECT id_buku,jumlah FROM tbl_detail_peminjaman WHERE id_peminjaman=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $ds = mysqli_stmt_get_result($stmt);
        while ($d = mysqli_fetch_assoc($ds)) {
            $u = mysqli_prepare($koneksi, "UPDATE tbl_buku SET stok=stok+? WHERE id_buku=?");
            mysqli_stmt_bind_param($u, "ii", $d["jumlah"], $d["id_buku"]);
            mysqli_stmt_execute($u);
            mysqli_stmt_close($u);
            log_activity($koneksi, (int)$_SESSION["id_user"], (int)$d["id_buku"], "pengembalian", "Mengembalikan buku untuk peminjaman #" . $id);
        }
        mysqli_stmt_close($stmt);
        $date = date("Y-m-d");
        $stmt = mysqli_prepare($koneksi, "UPDATE tbl_peminjaman SET tanggal_kembali=?,status='selesai' WHERE id_peminjaman=?");
        mysqli_stmt_bind_param($stmt, "si", $date, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_commit($koneksi);
        flash("Buku berhasil dikembalikan.");
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        flash("Peminjaman tidak dapat diproses.", "error");
    }
    redirect("admin/peminjaman/index.php");
}
$loans = mysqli_query($koneksi, "SELECT p.*,u.fullname,GROUP_CONCAT(b.judul_buku SEPARATOR ', ') buku FROM tbl_peminjaman p JOIN tbl_user u ON u.id_user=p.id_anggota JOIN tbl_detail_peminjaman d ON d.id_peminjaman=p.id_peminjaman JOIN tbl_buku b ON b.id_buku=d.id_buku GROUP BY p.id_peminjaman ORDER BY p.id_peminjaman DESC");
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Peminjaman</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
</head>

<body><?php render_flash(); ?><?php include __DIR__ . "/../partials/sidebar.php"; ?><main class="main">
        <h1>Peminjaman</h1>
        <div class="card">
            <table>
                <tr>
                    <th>User</th>
                    <th>Buku</th>
                    <th>Pinjam</th>
                    <th>Tenggat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr><?php while ($p = mysqli_fetch_assoc($loans)): ?><tr>
                        <td><?= e($p["fullname"]) ?></td>
                        <td><?= e($p["buku"]) ?></td>
                        <td><?= $p["tanggal_pinjam"] ?></td>
                        <td><?= $p["tanggal_tenggat"] ?></td>
                        <td><?= $p["status"] ?></td>
                        <td><?php if ($p["status"] === "dipinjam"): ?><a class="button" onclick="return confirm('Kembalikan buku?')" href="?kembali=<?= $p["id_peminjaman"] ?>">Kembalikan</a><?php endif; ?></td>
                    </tr><?php endwhile; ?>
            </table>
        </div>
    </main>
</body>

</html>