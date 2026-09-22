<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
if($_SERVER["REQUEST_METHOD"]!=="POST") redirect("user/buku/index.php");
$u=(int)$_SESSION["id_user"];$b=(int)($_POST["id_buku"]??0);$hapus=($_POST["aksi"]??"")==="hapus";
$stmt=mysqli_prepare($koneksi,$hapus?"DELETE FROM tbl_favorit WHERE id_user=? AND id_buku=?":"INSERT IGNORE INTO tbl_favorit(id_user,id_buku) VALUES(?,?)");
mysqli_stmt_bind_param($stmt,"ii",$u,$b);mysqli_stmt_execute($stmt);mysqli_stmt_close($stmt);
redirect("user/buku/detail.php?id=".$b);
