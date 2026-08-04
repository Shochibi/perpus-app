<?php
require '../../config/koneksi.php';

// 1. Cek apakah ada parameter id_buku di URL
if (isset($_GET['id_buku'])) {
    $id_buku = $_GET['id_buku'];

    // 2. Ambil data nama file gambar dari database sebelum bukunya dihapus
    $query_select = mysqli_query($koneksi, "SELECT cover_buku FROM tbl_buku WHERE id_buku='$id_buku'");
    $data = mysqli_fetch_object($query_select);

    if ($data) {
        $file_gambar = $data->cover_buku;
        $path_gambar = "../../images/" . $file_gambar;

        // 3. Hapus file gambar dari folder images (jika filenya ada)
        if (!empty($file_gambar) && file_exists($path_gambar)) {
            unlink($path_gambar); // Fungsi unlink() digunakan untuk menghapus file
        }

        // 4. Hapus data buku dari database
        $query_delete = "DELETE FROM tbl_buku WHERE id_buku='$id_buku'";

        if (mysqli_query($koneksi, $query_delete)) {
            echo "<script>
                    alert('Data buku berhasil dihapus!');
                    window.location.href = 'buku.php';
                  </script>";
        } else {
            echo "Gagal menghapus data: " . mysqli_error($koneksi);
        }
    } else {
        echo "<script>
                alert('Data buku tidak ditemukan!');
                window.location.href = 'buku.php';
              </script>";
    }
} else {
    // Jika mencoba akses langsung tanpa id_buku
    header("Location: ../index.php?page=buku");
    exit();
}