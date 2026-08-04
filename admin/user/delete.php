<?php
session_start();
if(!isset($_SESSION['id_user'])){
    header("Location: ../../auth/login.php");
    exit;
}
include '../../config/koneksi.php';

if(isset($_GET['id_user'])) {
    $id_user = $_GET['id_user'];
    $sql = "DELETE FROM tbl_user WHERE id_user='$id_user'";

    $result = mysqli_query($koneksi, $sql);
    if($result) {
        echo "<script>
        alert('Data Berhasil Dihapus!');
        window.location.href = 'user.php';
        </script>";
    } else {
        echo "<script>
        alert('Data Gagal Dihapus!');
        window.location.href = 'user.php';
        </script>";
    }
}