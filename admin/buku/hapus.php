<?php

require_once '../../middleware/admin.php';
require_once '../../config/koneksi.php';

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Cek apakah buku sedang dipinjam
|--------------------------------------------------------------------------
*/

$cek = mysqli_query(
    $koneksi,
    "
    SELECT
        COUNT(*) AS total
    FROM tbl_detail_peminjaman
    INNER JOIN tbl_peminjaman
        ON tbl_detail_peminjaman.id_peminjaman =
           tbl_peminjaman.id_peminjaman
    WHERE tbl_detail_peminjaman.id_buku = $id
    AND tbl_peminjaman.status = 'dipinjam'
    "
);

$data = mysqli_fetch_assoc($cek);

if ($data['total'] > 0) {
    flash("Buku tidak dapat dihapus karena masih sedang dipinjam.", "error");
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil data buku
|--------------------------------------------------------------------------
*/

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tbl_buku WHERE id_buku = $id"
);

$buku = mysqli_fetch_assoc($query);

if (!$buku) {

    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Hapus cover
|--------------------------------------------------------------------------
*/

if (
    !empty($buku['cover_buku']) &&
    file_exists(
        '../../uploads/cover/' .
            $buku['cover_buku']
    )
) {

    unlink(
        '../../uploads/cover/' .
            $buku['cover_buku']
    );
}

/*
|--------------------------------------------------------------------------
| Hapus PDF
|--------------------------------------------------------------------------
*/

if (
    !empty($buku['pdf_buku']) &&
    file_exists(
        '../../uploads/pdf/' .
            $buku['pdf_buku']
    )
) {

    unlink(
        '../../uploads/pdf/' .
            $buku['pdf_buku']
    );
}

/*
|--------------------------------------------------------------------------
| Hapus database
|--------------------------------------------------------------------------
*/

log_activity($koneksi, (int)$_SESSION["id_user"], $id, "hapus_buku", "Menghapus buku: " . $buku["judul_buku"]);

mysqli_query(
    $koneksi,
    "DELETE FROM tbl_buku WHERE id_buku = $id"
);

flash("Buku berhasil dihapus.");
header("Location: index.php");
exit;
