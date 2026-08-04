<?php
require '../../config/koneksi.php';

$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $query = mysqli_query($koneksi, "SELECT * FROM tbl_buku WHERE id_buku='$id_edit'");
    $edit_data = mysqli_fetch_object($query);
}
?>

<h1><?= isset($edit_data) ? "Edit Buku" : "Tambah Buku" ?></h1>

<form action="<?= isset($edit_data) ? "update.php" : "create.php" ?>" method="POST" enctype="multipart/form-data">

    <label for="judul">Judul Buku</label>
    <input type="text" name="judul" id="judul" placeholder="Masukkan judul buku"
        value="<?= isset($edit_data) ? $edit_data->judul : "" ?>" required>

    <label for="penulis">Penulis</label>
    <input type="text" name="penulis" id="penulis" placeholder="Masukkan nama penulis"
        value="<?= isset($edit_data) ? $edit_data->penulis : "" ?>" required>

    <label for="penerbit">Penerbit</label>
    <input type="text" name="penerbit" id="penerbit" placeholder="Masukkan penerbit"
        value="<?= isset($edit_data) ? $edit_data->penerbit : "" ?>" required>

    <label for="tahun_terbit">Tahun Terbit</label>
    <input type="number" name="tahun_terbit" id="tahun_terbit" placeholder="Masukkan tahun terbit"
        value="<?= isset($edit_data) ? $edit_data->tahun_terbit : "" ?>" required>

    <label for="id_kategori">Kategori Buku</label>
    <select name="id_kategori" id="id_kategori" required>
        <option value="">-- Pilih Kategori --</option>
        <?php
        $query_kategori = mysqli_query($koneksi, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");
        while ($kat = mysqli_fetch_object($query_kategori)) {
            $selected = (isset($edit_data) && $edit_data->id_kategori == $kat->id_kategori) ? "selected" : "";
            echo "<option value='{$kat->id_kategori}' {$selected}>{$kat->nama_kategori}</option>";
        }
        ?>
    </select>

    <label for="stok">Stok Buku</label>
    <input type="number" name="stok" id="stok" placeholder="Masukkan jumlah stok"
        value="<?= isset($edit_data) ? $edit_data->stok : "" ?>" required>

    <label for="cover_buku">Cover Buku</label>
    <input type="file" name="cover_buku" id="cover_buku" <?= isset($edit_data) ? "" : "required" ?>>
    <?php if (isset($edit_data) && !empty($edit_data->cover_buku)) : ?>
        <br><small>Cover saat ini: <?= $edit_data->cover_buku ?></small>
    <?php endif; ?>

    <br><br>
    <input type="submit" value="<?= isset($edit_data) ? "Update" : "Tambah" ?>" class="btn-buku">

    <?php if (isset($edit_data)) : ?>
        <input type="hidden" name="id_buku" id="id_buku" value="<?= $edit_data->id_buku ?>">
        <a href="buku.php" style="display: inline-block; margin-left: 10px; text-decoration: none; padding: 8px 15px; background-color: #6c757d; color: white; border-radius: 4px;">Batal</a>
    <?php endif; ?>
</form>

<hr style="margin: 20px 0;">

<div class="search-box">
    <form action="" method="GET">
        <input type="text" name="cari" placeholder="Cari judul / penulis" value="<?= isset($_GET['cari']) ? $_GET['cari'] : "" ?>">
        <button type="submit" class="cari-btn">Cari</button>
        <a href="buku.php">Reset</a>
    </form>
</div>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>NO</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Penerbit</th>
            <th>Tahun</th>
            <th>Kategori</th>
            <th>Cover</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;

        if (isset($_GET['cari']) && $_GET['cari'] != '') {
            $cari = $_GET['cari'];
            $sql = "SELECT tbl_buku.*, tbl_kategori.nama_kategori 
                    FROM tbl_buku 
                    LEFT JOIN tbl_kategori ON tbl_buku.id_kategori = tbl_kategori.id_kategori 
                    WHERE tbl_buku.judul LIKE '%$cari%' OR tbl_buku.penulis LIKE '%$cari%'";
        } else {
            $sql = "SELECT tbl_buku.*, tbl_kategori.nama_kategori 
                    FROM tbl_buku 
                    LEFT JOIN tbl_kategori ON tbl_buku.id_kategori = tbl_kategori.id_kategori";
        }

        $result = mysqli_query($koneksi, $sql);
        while ($row = mysqli_fetch_object($result)) {
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row->judul ?></td>
                <td><?= $row->penulis ?></td>
                <td><?= $row->penerbit ?></td>
                <td><?= $row->tahun_terbit ?></td>
                <td><?= $row->nama_kategori ?? '-' ?></td>
                <td>
                    <img src="../../images/<?= $row->cover_buku ?>" alt="cover" width="60">
                </td>
                <td><?= $row->stok ?></td>
                <td>
                    <a href="buku.php?edit=<?= $row->id_buku ?>">Edit</a>
                    <a href="delete.php?id_buku=<?= $row->id_buku ?>" class="aksi-btn" onclick="return confirm('Hapus Data?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>