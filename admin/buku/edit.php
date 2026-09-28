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

    $stok = (int) $buku['stok'];

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

        flash("Buku berhasil diperbarui.");
        header("Location: index.php");
        exit;
    } else {

        flash("Gagal memperbarui buku.", "error");
        header("Location: edit.php?id=" . $id);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Buku</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
</head>
<body class="admin-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/sidebar.php"; ?>
    <main class="main admin-form-page admin-add-book">
        <div class="admin-page-heading"><div><span class="admin-kicker">MANAJEMEN KOLEKSI / EDIT BUKU</span><h1>Edit buku</h1><p>Perbarui informasi dan file buku yang sudah ada.</p></div><a class="admin-add-book__back" href="index.php">← Kembali ke daftar</a></div>
        <form method="POST" enctype="multipart/form-data" class="admin-add-book__layout">
            <div class="admin-add-book__main">
                <section class="admin-add-book__section"><div class="admin-add-book__section-head"><span>01</span><div><h2>Informasi buku</h2><p>Data utama yang tampil di katalog.</p></div></div>
                    <div class="admin-add-book__fields">
                        <div class="admin-add-book__field admin-add-book__full"><label for="judul_buku">Judul buku <em>*</em></label><input id="judul_buku" type="text" name="judul_buku" value="<?= htmlspecialchars($buku['judul_buku'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="admin-add-book__field"><label for="penulis_buku">Penulis <em>*</em></label><input id="penulis_buku" type="text" name="penulis_buku" value="<?= htmlspecialchars($buku['penulis_buku'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="admin-add-book__field"><label for="penerbit_buku">Penerbit</label><input id="penerbit_buku" type="text" name="penerbit_buku" value="<?= htmlspecialchars($buku['penerbit_buku'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="admin-add-book__field"><label for="id_kategori">Kategori <em>*</em></label><select id="id_kategori" name="id_kategori" required><?php while ($data = mysqli_fetch_assoc($kategori)): ?><option value="<?= (int)$data['id_kategori'] ?>" <?= (int)$data['id_kategori'] === (int)$buku['id_kategori'] ? 'selected' : '' ?>><?= htmlspecialchars($data['nama_kategori']) ?></option><?php endwhile; ?></select></div>
                        <div class="admin-add-book__field"><label for="tahun_terbit">Tahun terbit <em>*</em></label><input id="tahun_terbit" type="number" name="tahun_terbit" min="1900" max="<?= date('Y') ?>" value="<?= (int)$buku['tahun_terbit'] ?>" required></div>
                        <div class="admin-add-book__field admin-add-book__full"><label for="deskripsi">Deskripsi</label><textarea id="deskripsi" name="deskripsi" rows="5"><?= htmlspecialchars($buku['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
                    </div>
                </section>
                <section class="admin-add-book__section"><div class="admin-add-book__section-head"><span>02</span><div><h2>File buku</h2><p>Biarkan kosong jika tidak ingin mengganti file.</p></div></div>
                    <div class="admin-add-book__fields"><div class="admin-add-book__field"><label for="cover_buku">Ganti cover buku</label><input id="cover_buku" type="file" name="cover_buku" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, atau WebP. Cover lama tetap digunakan jika kosong.</small></div><div class="admin-add-book__field"><label for="pdf_buku">Ganti file PDF</label><input id="pdf_buku" type="file" name="pdf_buku" accept="application/pdf"><small>PDF lama tetap digunakan jika kosong.</small><?php if (!empty($buku['pdf_buku'])): ?><a class="admin-add-book__file-link" href="<?= BASE_URL ?>/uploads/pdf/<?= rawurlencode($buku['pdf_buku']) ?>" target="_blank" rel="noopener">Lihat PDF saat ini ↗</a><?php endif; ?></div></div>
                </section>
            </div>
            <aside class="admin-add-book__side"><section class="admin-add-book__section admin-add-book__preview"><div class="admin-add-book__section-head"><span>▤</span><div><h2>Pratinjau katalog</h2><p>Gambaran kartu buku untuk pembaca.</p></div></div><div class="admin-add-book__mock-cover" id="cover-preview"><?php if (!empty($buku['cover_buku'])): ?><img src="<?= BASE_URL ?>/uploads/cover/<?= rawurlencode($buku['cover_buku']) ?>" alt="Cover buku saat ini"><?php else: ?><span>▤<small>Cover buku</small></span><?php endif; ?></div><strong id="title-preview"><?= htmlspecialchars($buku['judul_buku']) ?></strong><p id="author-preview"><?= htmlspecialchars($buku['penulis_buku']) ?></p></section>
                <div class="admin-add-book__actions"><button type="submit" name="edit">✓ Simpan perubahan</button><a href="index.php">Batal</a></div>
            </aside>
        </form>
    </main>
<script>
(() => {
    const title = document.getElementById('judul_buku');
    const author = document.getElementById('penulis_buku');
    const file = document.getElementById('cover_buku');
    const cover = document.getElementById('cover-preview');
    const original = cover.innerHTML;
    let previewUrl;
    const update = () => {
        document.getElementById('title-preview').textContent = title.value.trim() || 'Judul buku akan muncul di sini';
        document.getElementById('author-preview').textContent = author.value.trim() || 'Nama penulis';
    };
    [title, author].forEach(input => input.addEventListener('input', update));
    file.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = file.files[0] && file.files[0].type.startsWith('image/') ? URL.createObjectURL(file.files[0]) : null;
        if (previewUrl) { const img = document.createElement('img'); img.src = previewUrl; img.alt = 'Pratinjau cover baru'; cover.replaceChildren(img); }
        else cover.innerHTML = original;
    });
})();
</script>
</body>
</html>
