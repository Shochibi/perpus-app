<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/koneksi.php";
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$idUser = isset($_SESSION["id_user"]) ? (int)$_SESSION["id_user"] : null;
log_activity($koneksi, $idUser, null, "logout", "Pengguna keluar dari aplikasi");
$_SESSION = [];
session_destroy();
redirect("auth/login.php");
