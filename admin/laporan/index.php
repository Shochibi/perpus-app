<?php
require_once __DIR__ . "/../../middleware/admin.php";
require_once __DIR__ . "/../../config/koneksi.php";
ensure_activity_logs($koneksi);

$aktivitasValid = [
    "register",
    "login",
    "logout",
    "membaca_buku",
    "tambah_buku",
    "ubah_buku",
    "hapus_buku",
];
$aktivitasDipilih = $_GET["aktivitas"] ?? [];
if (!is_array($aktivitasDipilih)) {
    $aktivitasDipilih = [$aktivitasDipilih];
}
$aktivitasDipilih = array_values(array_intersect($aktivitasDipilih, $aktivitasValid));
$role = $_GET["role"] ?? "";
if (!in_array($role, ["user", "admin"], true)) {
    $role = "";
}
$tanggalMulai = $_GET["tanggal_mulai"] ?? "";
$tanggalSelesai = $_GET["tanggal_selesai"] ?? "";
$tanggalValid = static function (string $tanggal): bool {
    $date = DateTimeImmutable::createFromFormat("!Y-m-d", $tanggal);
    return $date !== false && $date->format("Y-m-d") === $tanggal;
};
if (!$tanggalValid($tanggalMulai)) {
    $tanggalMulai = "";
}
if (!$tanggalValid($tanggalSelesai)) {
    $tanggalSelesai = "";
}
if ($tanggalMulai !== "" && $tanggalSelesai !== "" && $tanggalMulai > $tanggalSelesai) {
    [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
}

$conditions = [];
$params = [];
$types = "";
if ($role !== "") {
    $conditions[] = "u.role=?";
    $types .= "s";
    $params[] = $role;
}
if ($aktivitasDipilih) {
    $conditions[] = "l.aktivitas IN (" . implode(",", array_fill(0, count($aktivitasDipilih), "?")) . ")";
    $types .= str_repeat("s", count($aktivitasDipilih));
    $params = array_merge($params, $aktivitasDipilih);
}
if ($tanggalMulai !== "") {
    $conditions[] = "l.created_at >= ?";
    $types .= "s";
    $params[] = $tanggalMulai . " 00:00:00";
}
if ($tanggalSelesai !== "") {
    $conditions[] = "l.created_at < DATE_ADD(?, INTERVAL 1 DAY)";
    $types .= "s";
    $params[] = $tanggalSelesai . " 00:00:00";
}
$where = $conditions ? " WHERE " . implode(" AND ", $conditions) : "";
$query = "SELECT l.*, u.fullname AS nama_user, u.role AS role_user, b.judul_buku FROM activity_logs l LEFT JOIN tbl_user u ON u.id_user=l.id_user LEFT JOIN tbl_buku b ON b.id_buku=l.id_buku$where ORDER BY l.created_at DESC, l.id_log DESC";
$stmt = mysqli_prepare($koneksi, $query);
if ($params) {
    $bindParams = [$stmt, $types];
    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }
    call_user_func_array("mysqli_stmt_bind_param", $bindParams);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$total = mysqli_num_rows($result);
$namaAktivitas = [
    "register" => "Register",
    "login" => "Login",
    "logout" => "Logout",
    "membaca_buku" => "Membaca buku",
    "tambah_buku" => "Tambah buku",
    "ubah_buku" => "Ubah buku",
    "hapus_buku" => "Hapus buku",
];
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Aktivitas</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
</head>

<body class="report-page">
    <?php include __DIR__ . "/../partials/sidebar.php"; ?>
    <main class="main">
        <div class="report-heading">
            <div>
                <h1>Laporan Aktivitas</h1>
                <p>Riwayat kegiatan pengguna dan pengelolaan buku.</p>
            </div>
            <div class="report-actions">
                <div class="report-total">
                    <strong><?= $total ?></strong>
                    <span>Aktivitas</span>
                </div>

                <a
                    class="report-download"
                    href="<?= BASE_URL ?>/admin/laporan/export_pdf.php?<?= e(http_build_query($_GET)) ?>">
                    Download PDF
                </a>
            </div>
        </div>

        <div class="card report-card">
            <form class="report-filter" method="get">
                <label for="role">Role akun</label>
                <select id="role" name="role">
                    <option value="">Semua role</option>
                    <option value="user" <?= $role === "user" ? "selected" : "" ?>>User</option>
                    <option value="admin" <?= $role === "admin" ? "selected" : "" ?>>Admin</option>
                </select>
                <fieldset class="period-options">
                    <legend>Periode</legend>
                    <label for="tanggal_mulai">Dari</label>
                    <input id="tanggal_mulai" type="date" name="tanggal_mulai" value="<?= e($tanggalMulai) ?>">
                    <label for="tanggal_selesai">Sampai</label>
                    <input id="tanggal_selesai" type="date" name="tanggal_selesai" value="<?= e($tanggalSelesai) ?>">
                </fieldset>
                <fieldset class="activity-options">
                    <legend>Aktivitas</legend>
                    <?php foreach ($namaAktivitas as $value => $label): ?>
                        <label><input type="checkbox" name="aktivitas[]" value="<?= e($value) ?>" <?= in_array($value, $aktivitasDipilih, true) ? "checked" : "" ?>> <?= e($label) ?></label>
                    <?php endforeach; ?>
                </fieldset>
                <?php if ($role !== "" || $aktivitasDipilih || $tanggalMulai !== "" || $tanggalSelesai !== ""): ?><a class="report-reset" href="<?= BASE_URL ?>/admin/laporan/index.php">Reset</a><?php endif; ?>
                <button class="report-filter-button" type="submit">Terapkan</button>
            </form>

            <div class="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aktivitas</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total === 0): ?>
                            <tr>
                                <td class="report-empty" colspan="4">Belum ada aktivitas yang tercatat.</td>
                            </tr>
                        <?php endif; ?>
                        <?php while ($log = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="report-date"><?= e(date("d-m-Y H:i", strtotime($log["created_at"]))) ?></td>
                                <td><?= e($log["nama_user"] ?? "User dihapus") ?><small class="report-role"><?= e(ucfirst($log["role_user"] ?? "-")) ?></small></td>
                                <td><span class="activity-badge activity-<?= e($log["aktivitas"]) ?>"><?= e($namaAktivitas[$log["aktivitas"]] ?? $log["aktivitas"]) ?></span></td>
                                <td>
                                    <?= e($log["keterangan"] ?? "-") ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>
