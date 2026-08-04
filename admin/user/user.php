<?php
require '../../config/koneksi.php';
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $query = mysqli_query($koneksi, "SELECT * FROM tbl_user WHERE id_user='$id_edit'");
    $edit_data = mysqli_fetch_object($query);
}
?>

<h1><?= isset($edit_data) ? "Edit User" : "Tambah user" ?></h1>
<form action="<?= isset($edit_data) ? "update.php" : "create.php" ?>" method="POST" enctype="multipart/form-data">

    <label for="fullname">Nama User</label>
    <input type="text" name="fullname" id="fullname" placeholder="Masukkan Nama User"
        value="<?= isset($edit_data) ? $edit_data->fullname : "" ?>" required>

    <label for="username">Username</label>
    <input type="text" name="username" id="username" placeholder="Masukkan Username"
        value="<?= isset($edit_data) ? $edit_data->username : "" ?>" required>

    <label for="password">Password</label>
    <input type="password" name="password" id="password" placeholder="Masukkan Password"
        value="<?= isset($edit_data) ? $edit_data->password : "" ?>" <?= isset($edit_data) ? "" : "required" ?>>

    <label for="role">Role</label>
    <select name="role" id="role" required>
        <option value="">-- Pilih Role --</option>
        <option value="admin" <?= (isset($edit_data) && $edit_data->role == 'admin') ? 'selected' : '' ?>>Admin</option>
        <option value="user" <?= (isset($edit_data) && $edit_data->role == 'user') ? 'selected' : '' ?>>User</option>
    </select>

    <input type="submit" value="<?= isset($edit_data) ? "Update" : "Tambah" ?>" class="btn-user">
    <?php if (isset($edit_data)) : ?>
        <input type="hidden" name="id_user" id="id_user" value="<?= $edit_data->id_user ?>">
    <?php endif; ?>

</form>

<div class="search-box">
    <form action="" method="GET">
        <input type="hidden" name="page" value="user">
        <input type="text" name="cari" placeholder="Cari nama user" value="<?= isset($_GET['cari']) ? $_GET['cari'] : "" ?>">
        <button type="submit" class="btn-cari">Cari</button>
        <a href="index.php?page=user">Reset</a>
    </form>
</div>

<table border="1">
    <thead>
        <tr>
            <th>NO</th>
            <th>Nama User</th>
            <th>Username</th>
            <th>Password</th>
            <th>Role</th>
            <th>Dibuat</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;

        if (isset($_GET['cari']) && $_GET['cari'] != '') {
            $cari = $_GET['cari'];
            // Perbaikan: Penambahan tanda petik tunggal pada query LIKE
            $sql = "SELECT * FROM tbl_user WHERE fullname LIKE '%$cari%' OR username LIKE '%$cari%'";
        } else {
            $sql = "SELECT * FROM tbl_user";
        }
        $result = mysqli_query($koneksi, $sql);
        while ($row = mysqli_fetch_object($result)) {
        ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row->fullname ?></td>
                <td><?= $row->username ?></td>
                <td><?= $row->password ?></td>
                <td><?= $row->role ?></td>
                <td><?= $row->tanggal_daftar ?></td>
                <td>
                    <a href="user.php?edit=<?= $row->id_user ?>" class="aksi-btn">Edit</a>
                    <a href="delete.php?id_user=<?= $row->id_user ?>" class="aksi-btn" onclick="return confirm('Hapus Data?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>