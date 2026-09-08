<?php

require_once '../../middleware/admin.php';
require_once '../../config/koneksi.php';

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

$result = mysqli_query(
    $koneksi,
    "SELECT * FROM tbl_buku WHERE id_buku = $id"
);

$buku = mysqli_fetch_assoc($result);

if (!$buku) {
    die("Buku tidak ditemukan.");
}

$kategori = mysqli_query(
    $koneksi,
    "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC"
);

if (isset($_POST['edit'])) {

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

    $stok = (int) $_POST['stok'];

    $deskripsi = mysqli_real_escape_string(
        $koneksi,
        $_POST['deskripsi']
    );

    $cover_baru = $buku['cover_buku'];

    /*
    |--------------------------------------------------------------------------
    | Upload Cover Baru
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['cover_buku']) &&
        $_FILES['cover_buku']['error'] === 0
    ) {

        $ekstensi = strtolower(
            pathinfo(
                $_FILES['cover_buku']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allowed = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        if (!in_array($ekstensi, $allowed)) {
            die("Format cover tidak diperbolehkan.");
        }

        $cover_baru =
            uniqid('cover_') .
            '.' .
            $ekstensi;

        move_uploaded_file(
            $_FILES['cover_buku']['tmp_name'],
            '../../uploads/cover/' . $cover_baru
        );

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
    }

    /*
    |--------------------------------------------------------------------------
    | Upload PDF Baru
    |--------------------------------------------------------------------------
    */

    $pdf_baru = $buku['pdf_buku'];

    if (
        isset($_FILES['pdf_buku']) &&
        $_FILES['pdf_buku']['error'] === 0
    ) {

        $ekstensi = strtolower(
            pathinfo(
                $_FILES['pdf_buku']['name'],
                PATHINFO_EXTENSION
            )
        );

        if ($ekstensi !== 'pdf') {
            die("File harus PDF.");
        }

        $pdf_baru =
            uniqid('buku_') .
            '.pdf';

        move_uploaded_file(
            $_FILES['pdf_buku']['tmp_name'],
            '../../uploads/pdf/' . $pdf_baru
        );

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
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    $query = mysqli_query(
        $koneksi,
        "UPDATE tbl_buku SET

            id_kategori = '$id_kategori',

            judul_buku = '$judul',

            penulis_buku = '$penulis',

            penerbit_buku = '$penerbit',

            deskripsi = '$deskripsi',

            tahun_terbit = '$tahun',

            cover_buku = '$cover_baru',

            pdf_buku = '$pdf_baru',

            stok = '$stok'

        WHERE id_buku = $id"
    );

    if ($query) {
        log_activity($koneksi, (int)$_SESSION["id_user"], $id, "ubah_buku", "Mengubah buku: " . $judul);

        header("Location: index.php");
        exit;
    } else {

        echo "Gagal mengupdate buku.";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Edit Buku</title>

</head>

<body>

    <h1>Edit Buku</h1>

    <form
        method="POST"
        enctype="multipart/form-data">

        <label>Judul Buku</label>

        <br>

        <input
            type="text"
            name="judul_buku"
            value="<?= htmlspecialchars($buku['judul_buku']); ?>"
            required>

        <br><br>


        <label>Penulis</label>

        <br>

        <input
            type="text"
            name="penulis_buku"
            value="<?= htmlspecialchars($buku['penulis_buku']); ?>"
            required>

        <br><br>


        <label>Penerbit</label>

        <br>

        <input
            type="text"
            name="penerbit_buku"
            value="<?= htmlspecialchars($buku['penerbit_buku']); ?>">

        <br><br>


        <label>Kategori</label>

        <br>

        <select name="id_kategori" required>

            <?php while ($data = mysqli_fetch_assoc($kategori)): ?>

                <option
                    value="<?= $data['id_kategori']; ?>"
                    <?= $data['id_kategori'] == $buku['id_kategori']
                        ? 'selected'
                        : ''; ?>>

                    <?= htmlspecialchars($data['nama_kategori']); ?>

                </option>

            <?php endwhile; ?>

        </select>

        <br><br>


        <label>Tahun Terbit</label>

        <br>

        <input
            type="number"
            name="tahun_terbit"
            value="<?= $buku['tahun_terbit']; ?>"
            required>

        <br><br>


        <label>Stok</label>

        <br>

        <input
            type="number"
            name="stok"
            min="0"
            value="<?= $buku['stok']; ?>"
            required>

        <br><br>


        <label>Deskripsi</label>

        <br>

        <textarea
            name="deskripsi"
            rows="6"
            cols="50"><?= htmlspecialchars($buku['deskripsi']); ?></textarea>

        <br><br>


        <label>Cover Baru</label>

        <br>

        <input
            type="file"
            name="cover_buku"
            accept="image/*">

        <br>

        <?php if (!empty($buku['cover_buku'])): ?>

            <img
                src="../../uploads/cover/<?= htmlspecialchars($buku['cover_buku']); ?>"
                width="100">

        <?php endif; ?>

        <br><br>


        <label>PDF Baru</label>

        <br>

        <input
            type="file"
            name="pdf_buku"
            accept="application/pdf">

        <br>

        <?php if (!empty($buku['pdf_buku'])): ?>

            <a
                href="../../uploads/pdf/<?= htmlspecialchars($buku['pdf_buku']); ?>"
                target="_blank">

                Lihat PDF sekarang

            </a>

        <?php endif; ?>

        <br><br>


        <button
            type="submit"
            name="edit">

            Simpan Perubahan

        </button>

        <a href="index.php">
            Batal
        </a>

    </form>

</body>

</html>