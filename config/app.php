     <?php
define("BASE_URL", "/perpus-app");

// ===== KONFIGURASI SISTEM PREMIUM =====
define("PREMIUM_PRICE", 14999);           // Harga premium dasar (Rp)
define("PREMIUM_DURATION", 30);           // Durasi premium dasar (hari)
define("PAYMENT_METHOD", "e-wallet");     // Metode pembayaran
define("ADMIN_EWALLET", "+62-812-3456-7890");  // Nomor E-Wallet Admin untuk penerimaan pembayaran
define("MAX_PREVIEW_PAGES", 10);          // Batas halaman preview untuk user gratis

define("PREMIUM_PACKAGES", [
    [
        "id" => "1-bulan",
        "label" => "1 Bulan",
        "price" => 14999,
        "days" => 30,
    ],
    [
        "id" => "3-bulan",
        "label" => "3 Bulan",
        "price" => 39999,
        "days" => 90,
    ],
    [
        "id" => "6-bulan",
        "label" => "6 Bulan",
        "price" => 59999,
        "days" => 180,
    ],
    [
        "id" => "12-bulan",
        "label" => "12 Bulan",
        "price" => 79999,
        "days" => 365,
    ],
]);

define("COVER_DIR", __DIR__ . "/../uploads/cover/");
define("PDF_DIR", __DIR__ . "/../uploads/pdf/");

function get_premium_packages(): array
{
    $packages = [];
    foreach (PREMIUM_PACKAGES as $package) {
        $price = (int) ($package["price"] ?? 0);
        $days = (int) ($package["days"] ?? 0);
        $basePrice = (int) PREMIUM_PRICE;
        $baseDays = (int) PREMIUM_DURATION;

        $monthlyEquivalent = $basePrice * max(1, $days / $baseDays);
        $discountPercent = $monthlyEquivalent > 0 ? round((($monthlyEquivalent - $price) / $monthlyEquivalent) * 100) : 0;

        $packages[] = [
            "id" => $package["id"],
            "label" => $package["label"],
            "price" => $price,
            "days" => $days,
            "discount_percent" => max(0, $discountPercent),
        ];
    }

    return $packages;
}

function e($value): string
{
	return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function get_pdf_page_count(string $pdfPath): int
{
    if (!is_file($pdfPath)) {
        return 0;
    }

    $content = @file_get_contents($pdfPath);
    if ($content === false || $content === "") {
        return 0;
    }

    if (preg_match_all('/\/Count\s+(\d+)/', $content, $matches)) {
        $counts = array_map('intval', $matches[1]);
        return !empty($counts) ? max($counts) : 0;
    }

    if (preg_match_all('/\/Type\s*\/Page\b/i', $content, $matches)) {
        return count($matches[0]);
    }

    return 0;
}

function redirect(string $path): never
{
	header("Location: " . BASE_URL . "/" . ltrim($path, "/"));
	exit;
}

function ensure_activity_logs(mysqli $koneksi): bool
{
	$query = "CREATE TABLE IF NOT EXISTS activity_logs (
		id_log INT AUTO_INCREMENT PRIMARY KEY,
		id_user INT NULL,
		id_buku INT NULL,
        aktivitas VARCHAR(40) NOT NULL,
		keterangan TEXT NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		INDEX idx_activity_user (id_user),
		INDEX idx_activity_book (id_buku),
		INDEX idx_activity_type (aktivitas),
		INDEX idx_activity_created (created_at)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (mysqli_query($koneksi, $query) !== true) return false;

    return mysqli_query($koneksi, "ALTER TABLE activity_logs MODIFY aktivitas VARCHAR(40) NOT NULL") === true;
}

function ensure_rating_table(mysqli $koneksi): bool
{
    $query = "CREATE TABLE IF NOT EXISTS tbl_buku_rating (
        id_rating INT AUTO_INCREMENT PRIMARY KEY,
        id_buku INT NOT NULL,
        id_user INT NOT NULL,
        rating TINYINT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_buku_user (id_buku, id_user),
        KEY idx_buku (id_buku),
        KEY idx_user (id_user),
        CONSTRAINT fk_rating_buku FOREIGN KEY (id_buku) REFERENCES tbl_buku (id_buku) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_rating_user FOREIGN KEY (id_user) REFERENCES tbl_user (id_user) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return mysqli_query($koneksi, $query) === true;
}

function ensure_reading_progress(mysqli $koneksi): bool
{
    $query = "CREATE TABLE IF NOT EXISTS reading_progress (
        id_progress INT AUTO_INCREMENT PRIMARY KEY,
        id_user INT NOT NULL,
        id_buku INT NOT NULL,
        halaman_terakhir INT NOT NULL DEFAULT 1,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_progress_user_book (id_user, id_buku),
        KEY idx_progress_book (id_buku)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return mysqli_query($koneksi, $query) === true;
}

function save_reading_progress(mysqli $koneksi, int $idUser, int $idBuku, int $page): void
{
    if (!ensure_reading_progress($koneksi)) return;

    $stmt = mysqli_prepare($koneksi, "INSERT INTO reading_progress (id_user, id_buku, halaman_terakhir) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE halaman_terakhir = VALUES(halaman_terakhir), updated_at = CURRENT_TIMESTAMP");
    if (!$stmt) return;
    mysqli_stmt_bind_param($stmt, "iii", $idUser, $idBuku, $page);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function log_activity(mysqli $koneksi, ?int $idUser, ?int $idBuku, string $aktivitas, ?string $keterangan = null): void
{
	if (!ensure_activity_logs($koneksi)) return;

	$stmt = mysqli_prepare($koneksi, "INSERT INTO activity_logs (id_user, id_buku, aktivitas, keterangan) VALUES (?, ?, ?, ?)");
	if (!$stmt) return;
	mysqli_stmt_bind_param($stmt, "iiss", $idUser, $idBuku, $aktivitas, $keterangan);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
}

function flash(string $message, string $type = "success"): void
{
	if (session_status() !== PHP_SESSION_ACTIVE) session_start();
	$_SESSION["flash"] = ["message" => $message, "type" => $type];
}
function render_flash(): void
{
	if (empty($_SESSION["flash"])) return;
	$flash = $_SESSION["flash"];
	unset($_SESSION["flash"]);
	$message = json_encode($flash["message"], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
	$type = e($flash["type"] ?? "success");
	echo "<script>document.addEventListener('DOMContentLoaded',function(){const n=document.createElement('div');n.className='js-notification {$type}';n.textContent={$message};document.body.appendChild(n);setTimeout(function(){n.classList.add('hide');setTimeout(function(){n.remove()},300)},3500)});</script>";
}
