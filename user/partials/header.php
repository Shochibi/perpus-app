<?php
$currentPath = $_SERVER["PHP_SELF"] ?? "";
$isHome = basename($currentPath) === "dashboard.php";
$isBooks = str_contains($currentPath, "/user/buku/");
$isHistory = str_contains($currentPath, "/user/riwayat/");
?>
<header class="header user-header">
	<a class="user-header__brand" href="<?= BASE_URL ?>/user/dashboard.php">
		<span class="user-header__logo" aria-hidden="true"><i class="fa-solid fa-book-open"></i></span>
		<span>Perpustakaan</span>
	</a>
	<nav class="user-header__nav" aria-label="Navigasi utama">
		<a class="<?= $isHome ? "is-active" : "" ?>" href="<?= BASE_URL ?>/user/dashboard.php">Beranda</a>
		<a class="<?= $isBooks ? "is-active" : "" ?>" href="<?= BASE_URL ?>/user/buku/index.php">Buku</a>
		<a class="<?= $isHistory ? "is-active" : "" ?>" href="<?= BASE_URL ?>/user/riwayat/index.php">Riwayat Bacaan</a>
	</nav>
	<div class="user-header__account">
		<span><?= e($_SESSION["fullname"] ?? "User") ?></span>
		<a class="user-header__logout" href="<?= BASE_URL ?>/auth/logout.php" onclick="return confirm('Yakin ingin logout?')" aria-label="Logout" title="Logout"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></a>
	</div>
</header>
