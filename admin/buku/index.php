<?php

require_once '../../middleware/admin.php';
require_once '../../config/koneksi.php';

$query = mysqli_query($koneksi, "
    SELECT 
        tbl_buku.*,
        tbl_kategori.nama_kategori
    FROM tbl_buku
    LEFT JOIN tbl_kategori 
        ON tbl_buku.id_kategori = tbl_kategori.id_kategori
    ORDER BY tbl_buku.id_buku DESC
");

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
</head>

<body class="admin-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/sidebar.php"; ?>
    <main class="main">

        <div class="top admin-page-heading">
            <div><span class="admin-kicker">MANAJEMEN KOLEKSI</span><h1>Kelola buku</h1><p>Temukan dan perbarui koleksi perpustakaan.</p></div>
            <a href="tambah.php" class="btn btn-tambah">
                + Tambah Buku
            </a>
        </div>

        <div class="admin-catalog-toolbar"><label for="admin-book-search">Cari buku</label><input id="admin-book-search" type="search" placeholder="Cari judul, penulis, atau kategori…" autocomplete="off"><span id="admin-book-count" aria-live="polite"></span></div>
        <div class="card admin-table-card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Cover</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Tahun</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($buku = mysqli_fetch_assoc($query)): ?>
                            <tr class="admin-book-row">
                                <td>
                                    <?= $no++; ?>
                                </td>
                                <td>

                                    <?php if (!empty($buku['cover_buku'])): ?>

                                        <img
                                            src="../../uploads/cover/<?= htmlspecialchars($buku['cover_buku']); ?>"
                                            alt="Cover" style="max-width: 100px; max-height: 150px;">

                                    <?php else: ?>

                                        Tidak ada cover

                                    <?php endif; ?>

                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($buku['judul_buku']); ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($buku['penulis_buku']); ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($buku['nama_kategori'] ?? '-'); ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($buku['tahun_terbit']); ?>
                                </td>
                                <td>
                                    <a
                                        href="detail.php?id=<?= $buku['id_buku']; ?>"
                                        class="btn btn-detail">
                                        Detail
                                    </a>

                                    <a
                                        href="edit.php?id=<?= $buku['id_buku']; ?>"
                                        class="btn btn-edit">
                                        Edit
                                    </a>

                                    <a
                                        href="hapus.php?id=<?= $buku['id_buku']; ?>"
                                        class="btn btn-hapus"
                                        onclick="return confirm('Yakin ingin menghapus buku ini?');">
                                        Hapus
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" style="text-align:center;">
                                Belum ada data buku.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>
        </div>
        <p id="admin-book-empty" class="admin-empty" hidden>Tidak ada buku yang cocok dengan pencarian.</p>
    </main>

<script>
const search = document.getElementById('admin-book-search');
const rows = [...document.querySelectorAll('.admin-book-row')];
const count = document.getElementById('admin-book-count');
const empty = document.getElementById('admin-book-empty');
function filterBooks() {
    const term = search.value.trim().toLocaleLowerCase('id');
    let visible = 0;
    rows.forEach(row => { const match = row.textContent.toLocaleLowerCase('id').includes(term); row.hidden = !match; if (match) visible++; });
    count.textContent = `${visible} dari ${rows.length} buku`;
    empty.hidden = visible !== 0 || rows.length === 0;
}
search.addEventListener('input', filterBooks);
filterBooks();
</script>
</body>

</html>