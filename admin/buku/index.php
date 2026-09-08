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
</head>

<body>
    <?php render_flash(); ?>

    <div class="top">
        <h1>Data Buku</h1>
        <a href="tambah.php" class="btn btn-tambah">
            + Tambah Buku
        </a>
    </div>

    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Cover</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Kategori</th>
                <th>Tahun</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($query) > 0): ?>
                <?php $no = 1; ?>
                <?php while ($buku = mysqli_fetch_assoc($query)): ?>
                    <tr>
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
                            <?= htmlspecialchars($buku['judul_buku']); ?>
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
                            <?= htmlspecialchars($buku['stok']); ?>
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
                    <td colspan="8" style="text-align:center;">
                        Belum ada data buku.
                    </td>

                </tr>

            <?php endif; ?>

        </tbody>

    </table>

</body>

</html>