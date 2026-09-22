<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";

$idUser = (int)$_SESSION["id_user"];
$idBuku = (int)($_POST["id_buku"] ?? 0);
$returnTo = $_POST["return_to"] ?? "detail.php?id=" . $idBuku;
$returnTo = str_replace(["\r", "\n"], "", $returnTo);

if ($idBuku < 1) {
    flash("Buku tidak ditemukan.", "error");
    redirect("user/buku/index.php");
}

$stmt = mysqli_prepare($koneksi, "SELECT id_bookmark FROM tbl_bookmark WHERE id_user=? AND id_buku=?");
mysqli_stmt_bind_param($stmt, "ii", $idUser, $idBuku);
mysqli_stmt_execute($stmt);
$bookmark = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($bookmark) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM tbl_bookmark WHERE id_bookmark=? AND id_user=?");
    mysqli_stmt_bind_param($stmt, "ii", $bookmark["id_bookmark"], $idUser);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    flash("Bookmark dihapus.");
} else {
    $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_bookmark (id_user, id_buku) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ii", $idUser, $idBuku);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    flash("Buku disimpan ke bookmark.");
}

header("Location: " . BASE_URL . "/user/buku/" . ltrim($returnTo, "/"));
exit;
