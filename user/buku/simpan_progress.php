<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";
header("Content-Type: application/json; charset=utf-8");
if ($_SERVER["REQUEST_METHOD"] !== "POST") { http_response_code(405); exit; }
$data=json_decode(file_get_contents("php://input"),true)?:[];
$idUser=(int)$_SESSION["id_user"];$idBuku=(int)($data["id_buku"]??0);
$halaman=max(1,(int)($data["halaman"]??1));$total=max(0,(int)($data["total_halaman"]??0));
if($idBuku<1||$total<1||$halaman>$total){http_response_code(422);echo json_encode(["ok"=>false]);exit;}
$status=$halaman>=$total?"selesai":"membaca";
$stmt=mysqli_prepare($koneksi,"INSERT INTO reading_progress(id_user,id_buku,halaman_terakhir,total_halaman,status,terakhir_dibaca) VALUES(?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE halaman_terakhir=VALUES(halaman_terakhir),total_halaman=VALUES(total_halaman),status=VALUES(status),terakhir_dibaca=NOW()");
mysqli_stmt_bind_param($stmt,"iiiis",$idUser,$idBuku,$halaman,$total,$status);$ok=mysqli_stmt_execute($stmt);mysqli_stmt_close($stmt);
echo json_encode(["ok"=>$ok,"status"=>$status]);
