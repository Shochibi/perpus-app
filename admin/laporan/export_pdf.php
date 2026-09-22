<?php

require_once __DIR__ . "/../../middleware/admin.php";
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/*
|--------------------------------------------------------------------------
| Validasi filter
|--------------------------------------------------------------------------
*/

$aktivitasValid = [
    "register",
    "login",
    "logout",
    "membaca_buku",
    "tambah_buku",
    "ubah_buku",
    "hapus_buku",
];

$namaAktivitas = [
    "register" => "Register",
    "login" => "Login",
    "logout" => "Logout",
    "membaca_buku" => "Membaca buku",
    "tambah_buku" => "Tambah buku",
    "ubah_buku" => "Ubah buku",
    "hapus_buku" => "Hapus buku",
];

$aktivitasDipilih = $_GET["aktivitas"] ?? [];

if (!is_array($aktivitasDipilih)) {
    $aktivitasDipilih = [$aktivitasDipilih];
}

$aktivitasDipilih = array_values(
    array_intersect($aktivitasDipilih, $aktivitasValid)
);

$role = $_GET["role"] ?? "";

if (!in_array($role, ["user", "admin"], true)) {
    $role = "";
}

$tanggalMulai = $_GET["tanggal_mulai"] ?? "";
$tanggalSelesai = $_GET["tanggal_selesai"] ?? "";

$tanggalValid = static function (string $tanggal): bool {
    $date = DateTimeImmutable::createFromFormat("!Y-m-d", $tanggal);

    return $date !== false
        && $date->format("Y-m-d") === $tanggal;
};

if (!$tanggalValid($tanggalMulai)) {
    $tanggalMulai = "";
}

if (!$tanggalValid($tanggalSelesai)) {
    $tanggalSelesai = "";
}

if (
    $tanggalMulai !== ""
    && $tanggalSelesai !== ""
    && $tanggalMulai > $tanggalSelesai
) {
    [$tanggalMulai, $tanggalSelesai] = [
        $tanggalSelesai,
        $tanggalMulai,
    ];
}

/*
|--------------------------------------------------------------------------
| Membuat query berdasarkan filter
|--------------------------------------------------------------------------
*/

$conditions = [];
$params = [];
$types = "";

if ($role !== "") {
    $conditions[] = "u.role = ?";
    $types .= "s";
    $params[] = $role;
}

if ($aktivitasDipilih) {
    $placeholders = implode(
        ",",
        array_fill(0, count($aktivitasDipilih), "?")
    );

    $conditions[] = "l.aktivitas IN ($placeholders)";
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

$where = $conditions
    ? " WHERE " . implode(" AND ", $conditions)
    : "";

$query = "
    SELECT
        l.*,
        u.fullname AS nama_user,
        u.role AS role_user,
        b.judul_buku
    FROM activity_logs l
    LEFT JOIN tbl_user u
        ON u.id_user = l.id_user
    LEFT JOIN tbl_buku b
        ON b.id_buku = l.id_buku
    $where
    ORDER BY l.created_at DESC, l.id_log DESC
";

$stmt = mysqli_prepare($koneksi, $query);

if (!$stmt) {
    exit("Gagal mempersiapkan laporan.");
}

if ($params) {
    $bindParams = [$stmt, $types];

    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }

    call_user_func_array(
        "mysqli_stmt_bind_param",
        $bindParams
    );
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$logs = [];

while ($log = mysqli_fetch_assoc($result)) {
    $logs[] = $log;
}

$total = count($logs);

/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function pdfEscape(?string $value): string
{
    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
}

function formatTanggalIndonesia(string $tanggal): string
{
    return date("d-m-Y", strtotime($tanggal));
}

/*
|--------------------------------------------------------------------------
| Menentukan keterangan periode dan filter
|--------------------------------------------------------------------------
*/

if ($tanggalMulai !== "" && $tanggalSelesai !== "") {
    $periode =
        formatTanggalIndonesia($tanggalMulai)
        . " s.d. "
        . formatTanggalIndonesia($tanggalSelesai);
} elseif ($tanggalMulai !== "") {
    $periode =
        "Mulai "
        . formatTanggalIndonesia($tanggalMulai);
} elseif ($tanggalSelesai !== "") {
    $periode =
        "Sampai "
        . formatTanggalIndonesia($tanggalSelesai);
} else {
    $periode = "Semua periode";
}

$roleTampil = $role !== ""
    ? ucfirst($role)
    : "Semua role";

if ($aktivitasDipilih) {
    $aktivitasTampil = [];

    foreach ($aktivitasDipilih as $aktivitas) {
        $aktivitasTampil[] =
            $namaAktivitas[$aktivitas] ?? $aktivitas;
    }

    $aktivitasTampil = implode(", ", $aktivitasTampil);
} else {
    $aktivitasTampil = "Semua aktivitas";
}

/*
|--------------------------------------------------------------------------
| Logo kop surat
|--------------------------------------------------------------------------
|
| Sesuaikan lokasi logo dengan struktur project kamu.
| Disarankan menggunakan PNG atau JPG.
|
*/

$logoPath = __DIR__ . "/../../img/logo-sekolah.png";
$logoData = "";

if (is_file($logoPath)) {
    $mime = mime_content_type($logoPath);
    $logoBase64 = base64_encode(file_get_contents($logoPath));
    $logoData = "data:$mime;base64,$logoBase64";
}

/*
|--------------------------------------------------------------------------
| Membuat HTML PDF
|--------------------------------------------------------------------------
*/

ob_start();
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4 potrait;
            margin: 25px 28px 35px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        .letterhead {
            width: 100%;
            margin-bottom: 6px;
            border-bottom: 3px solid #111827;
        }

        .letterhead td {
            border: none;
            vertical-align: middle;
        }

        .letterhead-logo {
            width: 90px;
            text-align: center;
        }

        .letterhead-logo img {
            width: 70px;
            height: auto;
        }

        .letterhead-content {
            padding-bottom: 8px;
            text-align: center;
        }

        .letterhead-content h1 {
            margin: 0 0 3px;
            font-size: 18px;
            text-transform: uppercase;
        }

        .letterhead-content h2 {
            margin: 0 0 4px;
            font-size: 14px;
        }

        .letterhead-content p {
            margin: 2px 0;
            font-size: 9px;
        }

        .document-title {
            margin: 20px 0 15px;
            text-align: center;
        }

        .document-title h3 {
            margin: 0 0 4px;
            font-size: 15px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .document-title p {
            margin: 0;
        }

        .information {
            width: 100%;
            margin-bottom: 15px;
        }

        .information td {
            padding: 2px 4px;
            border: none;
            vertical-align: top;
        }

        .information-label {
            width: 90px;
            font-weight: bold;
        }

        .information-separator {
            width: 10px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table thead {
            display: table-header-group;
        }

        .report-table tr {
            page-break-inside: avoid;
        }

        .report-table th {
            padding: 7px 6px;
            color: #ffffff;
            background: #1e3a5f;
            border: 1px solid #111827;
            font-size: 9px;
            text-align: center;
        }

        .report-table td {
            padding: 6px;
            border: 1px solid #6b7280;
            font-size: 9px;
            vertical-align: top;
        }

        .number-column {
            width: 28px;
            text-align: center;
        }

        .time-column {
            width: 92px;
            white-space: nowrap;
        }

        .user-column {
            width: 120px;
        }

        .activity-column {
            width: 90px;
        }

        .role {
            color: #4b5563;
            font-size: 8px;
        }

        .empty {
            padding: 20px !important;
            text-align: center;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -20px;
            left: 0;
            color: #6b7280;
            font-size: 8px;
            text-align: center;
        }
    </style>
</head>

<body>

    <table class="letterhead">
        <tr>
            <td class="letterhead-logo">
                <?php if ($logoData !== ""): ?>
                    <img
                        src="<?= pdfEscape($logoData) ?>"
                        alt="Logo">
                <?php endif; ?>
            </td>

            <td class="letterhead-content">
                <!-- Ganti informasi kop surat berikut -->
                <h1>SMK Taruna Bangsa Kota Bekasi</h1>
                <h2>Perpustakaan Sekolah</h2>

                <p>
                    Jl. Lingkar Utara (Kaliabang Tengah), Kec. Bekasi Utara,
                    Kota Bekasi, Jawa Barat
                </p>

                <p>
                    Email: smktarunabangsa@gmail.com |
                    Telepon: (021) 88981166
                </p>
            </td>

            <!-- Penyeimbang agar tulisan kop tetap di tengah -->
            <td class="letterhead-logo"></td>
        </tr>
    </table>

    <div class="document-title">
        <h3>Laporan Aktivitas Perpustakaan</h3>
        <p>
            Dicetak pada
            <?= date("d-m-Y H:i") ?>
        </p>
    </div>

    <table class="information">
        <tr>
            <td class="information-label">Periode</td>
            <td class="information-separator">:</td>
            <td><?= pdfEscape($periode) ?></td>
        </tr>

        <tr>
            <td class="information-label">Role akun</td>
            <td class="information-separator">:</td>
            <td><?= pdfEscape($roleTampil) ?></td>
        </tr>

        <tr>
            <td class="information-label">Aktivitas</td>
            <td class="information-separator">:</td>
            <td><?= pdfEscape($aktivitasTampil) ?></td>
        </tr>

        <tr>
            <td class="information-label">Jumlah data</td>
            <td class="information-separator">:</td>
            <td><?= $total ?> aktivitas</td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th class="number-column">No.</th>
                <th class="time-column">Waktu</th>
                <th class="user-column">Pengguna</th>
                <th class="activity-column">Aktivitas</th>
                <th>Keterangan</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($total === 0): ?>
                <tr>
                    <td colspan="5" class="empty">
                        Belum ada aktivitas yang tercatat.
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($logs as $index => $log): ?>
                <?php
                $keterangan = $log["keterangan"] ?? "-";
                ?>

                <tr>
                    <td class="number-column">
                        <?= $index + 1 ?>
                    </td>

                    <td class="time-column">
                        <?= pdfEscape(
                            date(
                                "d-m-Y H:i",
                                strtotime($log["created_at"])
                            )
                        ) ?>
                    </td>

                    <td>
                        <?= pdfEscape(
                            $log["nama_user"] ?? "User dihapus"
                        ) ?>

                        <div class="role">
                            <?= pdfEscape(
                                ucfirst($log["role_user"] ?? "-")
                            ) ?>
                        </div>
                    </td>

                    <td>
                        <?= pdfEscape(
                            $namaAktivitas[$log["aktivitas"]]
                                ?? $log["aktivitas"]
                        ) ?>
                    </td>

                    <td>
                        <?= pdfEscape($keterangan) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Laporan Aktivitas Aplikasi Perpustakaan
    </div>

</body>

</html>

<?php
$html = ob_get_clean();

/*
|--------------------------------------------------------------------------
| Generate dan download PDF
|--------------------------------------------------------------------------
*/

$options = new Options();
$options->set("defaultFont", "DejaVu Sans");

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, "UTF-8");

// Mengatur kertas A4 landscape
$dompdf->setPaper("A4", "potrait");

$dompdf->render();

$namaFile = "laporan-aktivitas-" . date("Y-m-d-His") . ".pdf";

$dompdf->stream($namaFile, [
    "Attachment" => true,
]);

exit;
