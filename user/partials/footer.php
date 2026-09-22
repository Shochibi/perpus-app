<?php $footerCssPath = __DIR__ . "/../../css/footer.css"; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime($footerCssPath) ?>">
<footer class="user-footer">
	<div class="user-footer__inner">
		<div class="user-footer__brand">
			<img class="user-footer__logo" src="<?= BASE_URL ?>/img/library-logo.svg" alt="">
			<div><strong>Perpustakaan</strong><span>Ruang baca digital.</span></div>
		</div>
		<div class="user-footer__column">
			<strong>Jelajahi</strong>
			<a href="<?= BASE_URL ?>/user/dashboard.php">Beranda</a>
			<a href="<?= BASE_URL ?>/user/buku/index.php">Semua Buku</a>
			<a href="<?= BASE_URL ?>/user/buku/bookmark.php">Bookmark</a>
		</div>
		<div class="user-footer__column">
			<strong>Layanan</strong>
			<a href="<?= BASE_URL ?>/user/pembayaran/index.php">Status Premium</a>
			<a href="<?= BASE_URL ?>/user/peminjaman/index.php">Peminjaman</a>
			<a href="<?= BASE_URL ?>/auth/logout.php">Keluar Akun</a>
		</div>
		<div class="user-footer__service">
			<strong>Mulai membaca</strong>
			<a href="<?= BASE_URL ?>/user/buku/index.php">Jelajahi koleksi <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
		</div>
	</div>
	<div class="user-footer__bottom">
		<span>&copy; <?= date("Y") ?> Perpus Digital</span>
		<span>Temukan cerita. Bangun wawasan.</span>
	</div>
</footer>
