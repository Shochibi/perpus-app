<?php
$currentPath = $_SERVER["PHP_SELF"] ?? "";
$isHome = basename($currentPath) === "dashboard.php";
$isBooks = str_contains($currentPath, "/user/buku/");
$showBookSearch = $isBooks && basename($currentPath) === "index.php";
$headerSearchBooks = [];
$headerCategories = [];
if (isset($koneksi)) {
	$headerSearchResult = mysqli_query($koneksi, "SELECT b.id_buku, b.judul_buku, b.penulis_buku, b.cover_buku, k.nama_kategori FROM tbl_buku b LEFT JOIN tbl_kategori k ON k.id_kategori=b.id_kategori ORDER BY b.id_buku DESC");
	if ($headerSearchResult) {
		while ($headerBook = mysqli_fetch_assoc($headerSearchResult)) {
			$headerSearchBooks[] = $headerBook;
		}
	}
	$headerCategoryResult = mysqli_query($koneksi, "SELECT nama_kategori FROM tbl_kategori ORDER BY nama_kategori ASC");
	if ($headerCategoryResult) {
		while ($headerCategory = mysqli_fetch_assoc($headerCategoryResult)) {
			$headerCategories[] = $headerCategory["nama_kategori"];
		}
	}
}
$isPembayaran = str_contains($currentPath, "/user/pembayaran/");
$accountIsPremium = false;
$accountDaysLeft = 0;
if (!empty($_SESSION["id_user"]) && isset($koneksi)) {
	$accountId = (int)$_SESSION["id_user"];
	$accountStmt = mysqli_prepare($koneksi, "SELECT is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=?");
	mysqli_stmt_bind_param($accountStmt, "i", $accountId);
	mysqli_stmt_execute($accountStmt);
	$accountData = mysqli_fetch_assoc(mysqli_stmt_get_result($accountStmt));
	mysqli_stmt_close($accountStmt);
	if ($accountData && $accountData["is_premium"] && !empty($accountData["tanggal_premium_hingga"])) {
		$accountExpiry = new DateTime($accountData["tanggal_premium_hingga"]);
		$accountIsPremium = new DateTime("today") <= $accountExpiry;
		$accountDaysLeft = (int)(new DateTime("today"))->diff($accountExpiry)->format("%r%a");
	}
}
?>
<header class="header user-header">
	<div class="user-header__brand">
		<a class="user-header__brand-link" href="<?= BASE_URL ?>/user/dashboard.php">
			<span class="user-header__logo" aria-hidden="true"><i class="fa-solid fa-book-open"></i></span>
			<span class="user-header__brand-copy"><strong>perpus.com</strong></span>
		</a>
	</div>
	<details class="user-category-menu">
		<summary>Kategori <i class="user-category-menu__chevron fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
		<div class="user-category-menu__panel">
			<div class="user-category-menu__tabs" role="tablist" aria-label="Jenis koleksi">
				<button class="is-active" type="button" role="tab" aria-selected="true" data-category-tab="buku">Buku</button>
				<button type="button" role="tab" aria-selected="false" data-category-tab="non-buku">Non-Buku</button>
			</div>
			<div class="user-category-menu__content is-active" data-category-content="buku">
				<div class="user-category-menu__carousel">
				<button class="user-category-menu__control user-category-menu__control--prev" type="button" aria-label="Kategori sebelumnya"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
				<div class="user-category-menu__grid">
					<a class="is-all-books" href="<?= BASE_URL ?>/user/buku/index.php">Semua Buku</a>
				<?php foreach ($headerCategories as $headerCategoryName): ?>
					<a href="<?= BASE_URL ?>/user/buku/index.php?q=<?= urlencode($headerCategoryName) ?>"><?= e($headerCategoryName) ?></a>
				<?php endforeach; ?>
				</div>
				<button class="user-category-menu__control user-category-menu__control--next" type="button" aria-label="Kategori berikutnya"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
				</div>
			</div>
			<div class="user-category-menu__content" data-category-content="non-buku" hidden>
				<p class="user-category-menu__empty">Koleksi non-buku belum tersedia.</p>
			</div>
		</div>
	</details>
	<form class="catalog-search user-header__search" method="get" action="<?= BASE_URL ?>/user/buku/index.php">
		<label class="catalog-search__field">
			<button type="submit" class="catalog-search__submit" aria-label="Cari buku"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
			<input name="q" value="<?= e($_GET["q"] ?? "") ?>" placeholder="Cari buku..." aria-label="Cari buku">
		</label>
		<div class="catalog-search__popover" id="catalogSearchPopover" aria-label="Hasil buku" hidden>
			<strong class="catalog-search__popover-title">Buku</strong>
			<div class="catalog-search__results" id="catalogSearchResults"></div>
		</div>
	</form>
	<div class="user-header__account">
		<details class="user-account-menu">
			<summary>
				<span class="user-account-menu__avatar user-account-menu__avatar--small"><i class="fa-solid fa-user" aria-hidden="true"></i></span>
			</summary>
			<div class="user-account-menu__preview">
				<div class="user-account-menu__top">
					<span class="user-account-menu__avatar"><i class="fa-solid fa-user" aria-hidden="true"></i></span>
					<div>
						<strong><?= e($_SESSION["fullname"] ?? "User") ?></strong>
						<span>@<?= e($_SESSION["username"] ?? "user") ?></span>
					</div>
				</div>
				<?php if ($accountIsPremium): ?>
					<a class="user-account-menu__premium" href="<?= BASE_URL ?>/user/pembayaran/index.php" title="Kelola atau perpanjang Premium"><span class="user-account-menu__crown"><i class="fa-solid fa-crown" aria-hidden="true"></i></span> Premium Aktif</a>
					<span class="user-account-menu__remaining">Sisa waktu: <?= $accountDaysLeft ?> hari</span>
				<?php else: ?>
					<a class="user-account-menu__upgrade" href="<?= BASE_URL ?>/user/pembayaran/beli_premium.php"><span class="user-account-menu__crown"><i class="fa-solid fa-crown" aria-hidden="true"></i></span> Upgrade</a>
				<?php endif; ?>
				<a class="user-account-menu__bookmark" href="<?= BASE_URL ?>/user/buku/bookmark.php"><i class="fa-solid fa-bookmark" aria-hidden="true"></i> Bookmark</a>
				<a class="user-header__logout" href="<?= BASE_URL ?>/auth/logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout</a>
			</div>
		</details>
	</div>
</header>
<script>
(() => {
	const headerSearchInput = document.querySelector('.user-header .catalog-search input[name="q"]');
	const headerSearchPopover = document.getElementById("catalogSearchPopover");
	const headerSearchResults = document.getElementById("catalogSearchResults");
	if (!headerSearchInput || !headerSearchPopover || !headerSearchResults) return;
	const headerSearchBooks = <?= json_encode($headerSearchBooks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
	const headerBaseUrl = <?= json_encode(BASE_URL) ?>;
	const escapeHeaderSearchText = (value) => String(value || "").replace(/[&<>'"]/g, (character) => ({
		"&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#039;", '"': "&quot;"
	})[character]);
	const renderHeaderSearch = () => {
		const keyword = headerSearchInput.value.trim().toLowerCase();
		const matches = headerSearchBooks.filter((book) => !keyword || [book.judul_buku, book.penulis_buku, book.nama_kategori].some((value) => String(value || "").toLowerCase().includes(keyword))).slice(0, keyword ? 8 : 4);
		headerSearchResults.innerHTML = matches.length ? matches.map((book) => `
			<a class="catalog-search__result" href="${headerBaseUrl}/user/buku/detail.php?id=${book.id_buku}">
				${book.cover_buku ? `<img src="${headerBaseUrl}/uploads/cover/${encodeURIComponent(book.cover_buku)}" alt="">` : `<span class="catalog-search__result-cover"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>`}
				<span class="catalog-search__result-copy"><span class="catalog-search__result-title">${escapeHeaderSearchText(book.judul_buku)}</span><span class="catalog-search__result-author">${escapeHeaderSearchText(book.penulis_buku)}</span></span>
			</a>`).join("") : '<span class="catalog-search__result-author">Buku tidak ditemukan.</span>';
		headerSearchPopover.hidden = false;
	};
	headerSearchInput.addEventListener("focus", renderHeaderSearch);
	headerSearchInput.addEventListener("input", renderHeaderSearch);
	document.querySelector('.user-header .catalog-search__submit').addEventListener("click", (event) => {
		event.preventDefault();
		headerSearchInput.focus();
		renderHeaderSearch();
	});
	document.addEventListener("click", (event) => {
		if (!event.target.closest(".user-header .catalog-search")) headerSearchPopover.hidden = true;
	});
})();
</script>
<script>
(() => {
	const categoryMenu = document.querySelector(".user-category-menu");
	const categoryGrid = document.querySelector(".user-category-menu__grid");
	const previousCategory = document.querySelector(".user-category-menu__control--prev");
	const nextCategory = document.querySelector(".user-category-menu__control--next");
	if (!categoryMenu || !categoryGrid || !previousCategory || !nextCategory) return;
	const categoryTabs = [...document.querySelectorAll("[data-category-tab]")];
	const categoryContents = [...document.querySelectorAll("[data-category-content]")];
	categoryTabs.forEach((tab) => tab.addEventListener("click", () => {
		const selected = tab.dataset.categoryTab;
		categoryTabs.forEach((item) => {
			const active = item === tab;
			item.classList.toggle("is-active", active);
			item.setAttribute("aria-selected", active ? "true" : "false");
		});
		categoryContents.forEach((content) => {
			content.hidden = content.dataset.categoryContent !== selected;
			content.classList.toggle("is-active", content.dataset.categoryContent === selected);
		});
	}));
	const updateCategoryControls = () => {
		previousCategory.disabled = categoryGrid.scrollLeft <= 1;
		nextCategory.disabled = categoryGrid.scrollLeft + categoryGrid.clientWidth >= categoryGrid.scrollWidth - 1;
	};
	previousCategory.addEventListener("click", () => categoryGrid.scrollBy({ left: -categoryGrid.clientWidth * .8, behavior: "smooth" }));
	nextCategory.addEventListener("click", () => categoryGrid.scrollBy({ left: categoryGrid.clientWidth * .8, behavior: "smooth" }));
	categoryGrid.addEventListener("scroll", updateCategoryControls, { passive: true });
	window.addEventListener("resize", updateCategoryControls);
	document.addEventListener("click", (event) => {
		if (!event.target.closest(".user-category-menu")) categoryMenu.removeAttribute("open");
	});
	document.addEventListener("keydown", (event) => {
		if (event.key === "Escape") categoryMenu.removeAttribute("open");
	});
	updateCategoryControls();
})();
</script>
