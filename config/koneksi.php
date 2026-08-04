<?php
$host = "localhost";
$user = "root";
$password = "";
$db_name = "db_perpustakaan";

$koneksi = new mysqli($host, $user, $password, $db_name);
if ($koneksi->connect_error) {
	die("koneksi gagal" . $koneksi->connect_error);
}