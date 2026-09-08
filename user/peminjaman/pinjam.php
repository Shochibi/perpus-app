<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
$idBuku = (int)($_GET["id"] ?? 0);
$idUser = (int)$_SESSION["id_user"];
mysqli_begin_transaction($koneksi);
try {
    $stmt = mysqli_prepare($koneksi, "SELECT stok FROM tbl_buku WHERE id_buku=? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "i", $idBuku);
    mysqli_stmt_execute($stmt);
    $b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$b || $b["stok"] < 1) throw new Exception("Buku tidak tersedia.");
    $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) n FROM tbl_detail_peminjaman d JOIN tbl_peminjaman p ON p.id_peminjaman=d.id_peminjaman WHERE p.id_anggota=? AND d.id_buku=? AND p.status='dipinjam'");
    mysqli_stmt_bind_param($stmt, "ii", $idUser, $idBuku);
    mysqli_stmt_execute($stmt);
    $n = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["n"];
    mysqli_stmt_close($stmt);
    if ($n) throw new Exception("Kamu masih meminjam buku ini.");
    $pinjam = date("Y-m-d");
    $tenggat = date("Y-m-d", strtotime("+7 days"));
    $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_peminjaman(id_anggota,tanggal_pinjam,tanggal_tenggat,status) VALUES(?,?,?,'dipinjam')");
    mysqli_stmt_bind_param($stmt, "iss", $idUser, $pinjam, $tenggat);
    mysqli_stmt_execute($stmt);
    $idP = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);
    $jumlah = 1;
    $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_detail_peminjaman(id_peminjaman,id_buku,jumlah) VALUES(?,?,?)");
    mysqli_stmt_bind_param($stmt, "iii", $idP, $idBuku, $jumlah);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $stmt = mysqli_prepare($koneksi, "UPDATE tbl_buku SET stok=stok-1 WHERE id_buku=?");
    mysqli_stmt_bind_param($stmt, "i", $idBuku);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    log_activity($koneksi, $idUser, $idBuku, "peminjaman", "Meminjam buku sampai " . $tenggat);
    mysqli_commit($koneksi);
    redirect("user/peminjaman/index.php");
} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    exit(e($e->getMessage()) . "<br><a href='" . BASE_URL . "/user/buku/detail.php?id=$idBuku'>Kembali</a>");
}
