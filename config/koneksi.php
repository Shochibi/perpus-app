<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_library";
$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) die("Koneksi gagal: " . mysqli_connect_error());
mysqli_set_charset($koneksi, "utf8mb4");
