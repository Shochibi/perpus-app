<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari form
    $id_user  = $_POST['id_user'];
    $fullname = mysqli_real_escape_string($koneksi, $_POST['fullname']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $role     = mysqli_real_escape_string($koneksi, $_POST['role']);

    // 2. Cek apakah password diisi atau dikosongkan
    if (!empty($password)) {
        // Jika password diisi/diubah: update password baru
        // Catatan: Disarankan memakai md5() atau password_hash() jika password di-hash di database
        $query = "UPDATE tbl_user SET 
                    fullname = '$fullname',
                    username = '$username',
                    password = '$password',
                    role     = '$role'
                  WHERE id_user = '$id_user'";
    } else {
        // Jika password dikosongkan saat edit: update data TANPA mengubah password lama
        $query = "UPDATE tbl_user SET 
                    fullname = '$fullname',
                    username = '$username',
                    role     = '$role'
                  WHERE id_user = '$id_user'";
    }

    // 3. Eksekusi query ke database
    if (mysqli_query($koneksi, $query)) {
        echo "<script>
                alert('Data user berhasil diperbarui!');
                window.location.href = 'user.php';
              </script>";
    } else {
        echo "Gagal memperbarui data user: " . mysqli_error($koneksi);
    }
} else {
    // Jika file diakses langsung tanpa method POST
    header("Location: user.php");
    exit();
}
