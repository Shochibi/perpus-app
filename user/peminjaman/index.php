<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
$id = (int)$_SESSION["id_user"];
$stmt = mysqli_prepare($koneksi, "SELECT p.*,GROUP_CONCAT(b.judul_buku SEPARATOR ', ') buku FROM tbl_peminjaman p JOIN tbl_detail_peminjaman d ON d.id_peminjaman=p.id_peminjaman JOIN tbl_buku b ON b.id_buku=d.id_buku WHERE p.id_anggota=? GROUP BY p.id_peminjaman ORDER BY p.id_peminjaman DESC");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$loans = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Peminjaman</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
</head>

<body>
    <header class="header"><a href="<?= BASE_URL ?>/user/dashboard.php">📚 Perpustakaan</a>
        <nav><a href="<?= BASE_URL ?>/user/buku/index.php">Buku</a><a href="<?= BASE_URL ?>/auth/logout.php">Logout</a></nav>
    </header>
    <main class="container">
        <h1>Peminjaman Saya</h1>
        <div class="card">
            <table>
                <tr>
                    <th>Buku</th>
                    <th>Pinjam</th>
                    <th>Tenggat</th>
                    <th>Kembali</th>
                    <th>Status</th>
                </tr><?php while ($p = mysqli_fetch_assoc($loans)): ?><tr>
                        <td><?= e($p["buku"]) ?></td>
                        <td><?= e($p["tanggal_pinjam"]) ?></td>
                        <td><?= e($p["tanggal_tenggat"]) ?></td>
                        <td><?= e($p["tanggal_kembali"] ?? "-") ?></td>
                        <td><?= e($p["status"]) ?></td>
                    </tr><?php endwhile; ?>
            </table>
        </div>
    </main>
</body>

</html>