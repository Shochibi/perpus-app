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

    $stok = 1;

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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Buku</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
</head>
<body class="admin-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/sidebar.php"; ?>
    <main class="main admin-form-page admin-add-book">
        <div class="admin-page-heading"><div><span class="admin-kicker">MANAJEMEN KOLEKSI / TAMBAH BUKU</span><h1>Tambah buku baru</h1><p>Lengkapi informasi buku agar mudah ditemukan pembaca.</p></div><a class="admin-add-book__back" href="index.php">← Kembali ke daftar</a></div>
        <form action="" method="POST" enctype="multipart/form-data" id="add-book-form" class="admin-add-book__layout">
            <div class="admin-add-book__main">
                <section class="admin-add-book__section"><div class="admin-add-book__section-head"><span>01</span><div><h2>Informasi buku</h2><p>Data utama yang akan tampil di katalog.</p></div></div>
                    <div class="admin-add-book__fields">
                        <div class="admin-add-book__field admin-add-book__full"><label for="judul_buku">Judul buku <em>*</em></label><input id="judul_buku" type="text" name="judul_buku" required placeholder="Contoh: Bumi Manusia"></div>
                        <div class="admin-add-book__field"><label for="penulis_buku">Penulis <em>*</em></label><input id="penulis_buku" type="text" name="penulis_buku" required placeholder="Nama penulis"></div>
                        <div class="admin-add-book__field"><label for="penerbit_buku">Penerbit</label><input id="penerbit_buku" type="text" name="penerbit_buku" placeholder="Nama penerbit"></div>
                        <div class="admin-add-book__field"><label for="id_kategori">Kategori <em>*</em></label><select id="id_kategori" name="id_kategori" required><option value="">Pilih kategori</option><?php while ($data = mysqli_fetch_assoc($kategori)): ?><option value="<?= (int)$data['id_kategori'] ?>"><?= htmlspecialchars($data['nama_kategori']) ?></option><?php endwhile; ?></select></div>
                        <div class="admin-add-book__field"><label for="tahun_terbit">Tahun terbit <em>*</em></label><input id="tahun_terbit" type="number" name="tahun_terbit" min="1900" max="<?= date('Y') ?>" required placeholder="<?= date('Y') ?>"></div>
                        <div class="admin-add-book__field admin-add-book__full"><label for="deskripsi">Deskripsi</label><textarea id="deskripsi" name="deskripsi" rows="5" placeholder="Ceritakan isi buku secara singkat..."></textarea></div>
                    </div>
                </section>
                <section class="admin-add-book__section"><div class="admin-add-book__section-head"><span>02</span><div><h2>File buku</h2><p>Tambahkan cover dan berkas bacaan jika tersedia.</p></div></div>
                    <div class="admin-add-book__fields"><div class="admin-add-book__field"><label for="cover_buku">Cover buku</label><input id="cover_buku" type="file" name="cover_buku" accept="image/jpeg,image/png,image/webp,image/avif"><small>JPG, PNG, WebP, atau AVIF.</small></div><div class="admin-add-book__field"><label for="pdf_buku">File PDF buku</label><input id="pdf_buku" type="file" name="pdf_buku" accept="application/pdf"><small>Unggah file PDF untuk dibaca pengguna.</small></div></div>
                </section>
            </div>
            <aside class="admin-add-book__side"><section class="admin-add-book__section admin-add-book__preview"><div class="admin-add-book__section-head"><span>▤</span><div><h2>Pratinjau katalog</h2><p>Gambaran kartu buku untuk pembaca.</p></div></div><div class="admin-add-book__mock-cover" id="cover-preview"><span>▤<small>Cover buku</small></span></div><strong id="title-preview">Judul buku akan muncul di sini</strong><p id="author-preview">Nama penulis</p></section>
                <div class="admin-add-book__actions"><button type="submit" name="tambah">＋ Simpan buku</button><a href="index.php">Batal</a></div>
            </aside>
        </form>
    </main>
<script>
(() => {
    const title = document.getElementById('judul_buku');
    const author = document.getElementById('penulis_buku');
    const file = document.getElementById('cover_buku');
    const cover = document.getElementById('cover-preview');
    let previewUrl;
    const update = () => {
        document.getElementById('title-preview').textContent = title.value.trim() || 'Judul buku akan muncul di sini';
        document.getElementById('author-preview').textContent = author.value.trim() || 'Nama penulis';
    };
    [title, author].forEach(input => input.addEventListener('input', update));
    file.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = file.files[0] && file.files[0].type.startsWith('image/') ? URL.createObjectURL(file.files[0]) : null;
        cover.replaceChildren();
        if (previewUrl) { const img = document.createElement('img'); img.src = previewUrl; img.alt = 'Pratinjau cover buku'; cover.append(img); }
        else { const label = document.createElement('span'); label.textContent = '▤ Cover buku'; cover.append(label); }
    });
})();
</script>
</body>
</html>
