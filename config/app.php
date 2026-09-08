<?php
define("BASE_URL", "/perpustakaan"); // sesuaikan dengan nama folder project

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
function log_activity(mysqli $koneksi, ?int $idUser, ?int $idBuku, string $aktivitas, ?string $keterangan = null): void
{
    $stmt = mysqli_prepare($koneksi, "INSERT INTO activity_logs (id_user, id_buku, aktivitas, keterangan) VALUES (?, ?, ?, ?)");
    if (!$stmt) return;
    mysqli_stmt_bind_param($stmt, "iiss", $idUser, $idBuku, $aktivitas, $keterangan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
