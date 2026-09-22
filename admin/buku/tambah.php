<?php

require_once '../../middleware/admin.php';
require_once '../../config/koneksi.php';

global $koneksi;

$kategori = mysqli_query(
    $koneksi,
    "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC"
);

if (isset($_POST['tambah'])) {

    $judul = mysqli_real_escape_string(
        $koneksi,
        $_POST['judul_buku']
    );

    $penulis = mysqli_real_escape_string(
        $koneksi,
        $_POST['penulis_buku']
    );

    $penerbit = mysqli_real_escape_string(
        $koneksi,
        $_POST['penerbit_buku']
    );

    $id_kategori = (int) $_POST['id_kategori'];

    $tahun = (int) $_POST['tahun_terbit'];

    $stok = 0;

    $deskripsi = mysqli_real_escape_string(
        $koneksi,
        $_POST['deskripsi']
    );

    /*
    |--------------------------------------------------------------------------
    | Upload Cover
    |--------------------------------------------------------------------------
    */

    $cover = $_FILES['cover_buku'];

    $nama_cover = null;

    if ($cover['error'] === 0) {

        $ekstensi = strtolower(
            pathinfo($cover['name'], PATHINFO_EXTENSION)
        );

        $ekstensi_allowed = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        if (!in_array($ekstensi, $ekstensi_allowed)) {
            die("Format cover tidak diperbolehkan.");
        }

        $nama_cover = uniqid('cover_') . '.' . $ekstensi;

        move_uploaded_file(
            $cover['tmp_name'],
            '../../uploads/cover/' . $nama_cover
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Upload PDF
    |--------------------------------------------------------------------------
    */

    $pdf = $_FILES['pdf_buku'];

    $nama_pdf = null;

    if ($pdf['error'] === 0) {

        $ekstensi = strtolower(
            pathinfo($pdf['name'], PATHINFO_EXTENSION)
        );

        if ($ekstensi !== 'pdf') {
            die("File buku harus berupa PDF.");
        }

        $nama_pdf = uniqid('buku_') . '.pdf';

        move_uploaded_file(
            $pdf['tmp_name'],
            '../../uploads/pdf/' . $nama_pdf
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Insert Database
    |--------------------------------------------------------------------------
    */

    $query = mysqli_query(
        $koneksi,
        "INSERT INTO tbl_buku
        (id_kategori,judul_buku,penulis_buku,penerbit_buku,
            deskripsi,
            tahun_terbit,
            cover_buku,
            pdf_buku,
            stok
        )
        VALUES
        (
            '$id_kategori',
            '$judul',
            '$penulis',
            '$penerbit',
            '$deskripsi',
            '$tahun',
            '$nama_cover',
            '$nama_pdf',
            '$stok'
        )"
    );

    if ($query) {
        $idBuku = mysqli_insert_id($koneksi);
        log_activity($koneksi, (int)$_SESSION["id_user"], $idBuku, "tambah_buku", "Menambahkan buku: " . $judul);

        flash("Buku berhasil ditambahkan.");
        header("Location: index.php");
        exit;
    } else {

        flash("Gagal menambahkan buku.", "error");
        header("Location: tambah.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Buku</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-buku.css">

</head>

<body>

    <?php include __DIR__ . "/../partials/sidebar.php"; ?>
    <main class="main book-admin">
    <header class="book-admin__top"><div><h1>Tambah Buku</h1><p>Lengkapi informasi dan unggah file buku digital.</p></div><a class="book-admin__button" href="index.php"><i class="fa-solid fa-arrow-left"></i> Kembali</a></header>
    <div class="book-form-layout">

    <form
        class="book-admin-form"
        action=""
        method="POST"
        enctype="multipart/form-data">

        <div>

            <label>Judul Buku</label>

            <br>

            <input
                type="text"
                name="judul_buku"
                required>

        </div>

        <br>

        <div>

            <label>Penulis</label>

            <br>

            <input
                type="text"
                name="penulis_buku"
                required>

        </div>

        <br>

        <div>

            <label>Penerbit</label>

            <br>

            <input
                type="text"
                name="penerbit_buku">

        </div>

        <br>

        <div>

            <label>Kategori</label>

            <br>

            <select name="id_kategori" required>

                <option value="">
                    -- Pilih Kategori --
                </option>

                <?php while ($data = mysqli_fetch_assoc($kategori)): ?>

                    <option value="<?= $data['id_kategori']; ?>">

                        <?= htmlspecialchars($data['nama_kategori']); ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>

        <br>

        <div>

            <label>Tahun Terbit</label>

            <br>

            <input
                type="number"
                name="tahun_terbit"
                min="1900"
                max="<?= date('Y'); ?>"
                required>

        </div>

        <br>

        <div>

            <label>Deskripsi</label>

            <br>

            <textarea
                name="deskripsi"
                rows="6"
                cols="50"></textarea>

        </div>

        <br>

        <div>

            <label>Cover Buku</label>

            <br>

            <input
                type="file"
                name="cover_buku"
                accept="image/*">

        </div>

        <br>

        <div>

            <label>File PDF Buku</label>

            <br>

            <input
                type="file"
                name="pdf_buku"
                accept="application/pdf">

        </div>

        <br>

        <div class="book-form-actions"><button
            type="submit"
            name="tambah">

            Tambah Buku

        </button>

        <a href="index.php">
            Batal
        </a></div>

    </form>

    <aside class="book-form-help"><h3><i class="fa-regular fa-lightbulb"></i> Panduan</h3><ul><li>Gunakan cover vertikal agar tampil rapi.</li><li>Format cover: JPG, PNG, WebP, atau AVIF.</li><li>Unggah satu file PDF lengkap untuk dibaca user.</li><li>Pastikan judul dan penulis sudah benar.</li></ul></aside>
    </div>
    </main>

</body>

</html>
