<?php
session_start();
if(!isset($_SESSION['fullname'])){
    header("Location: ../../auth/login.php");
    exit;
}
require '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$fullname = $_POST['fullname'];
	$username = $_POST['username'];
	$password = $_POST['password'];
    $role = $_POST['role'];

	$cek = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM tbl_user WHERE username='$username'"));
	if ($cek > 0) {
		echo "<script>
		alert('Username Sudah Terpakai');
		window.location.href = 'user.php';
		</script>";
	} else {
		mysqli_query($koneksi, "INSERT INTO tbl_user (fullname, username, password, role, tanggal_daftar) 
		VALUES ('$fullname', '$username', '$password', '$role', CURDATE())");
		echo "<script>
		alert('Berhasil Daftar!');
		window.location.href = 'user.php';
		</script>";
	}
}
