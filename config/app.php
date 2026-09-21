<?php
define("BASE_URL", "/perpustakaan");

// ===== KONFIGURASI SISTEM PREMIUM =====
define("PREMIUM_PRICE", 50000);           // Harga premium (Rp)
define("PREMIUM_DURATION", 30);           // Durasi premium (hari)
define("PAYMENT_METHOD", "e-wallet");     // Metode pembayaran
define("ADMIN_EWALLET", "+62-812-3456-7890");  // Nomor E-Wallet Admin untuk penerimaan pembayaran
define("MAX_PREVIEW_PAGES", 10);          // Batas halaman preview untuk user gratis

define("COVER_DIR", __DIR__ . "/../uploads/cover/");
define("PDF_DIR", __DIR__ . "/../uploads/pdf/");
function e($value): string
{
	return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
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

	return mysqli_query($koneksi, $query) === true;
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
