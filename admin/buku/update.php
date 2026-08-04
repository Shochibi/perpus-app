<?php
require '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari form
    $id_buku      = $_POST['id_buku'];
    $judul        = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis      = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $penerbit     = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $stok         = $_POST['stok'];
    $id_kategori  = $_POST['id_kategori'];

    // 2. Cek apakah pengguna mengunggah cover buku baru
    $nama_cover = $_FILES['cover_buku']['name'];
    $tmp_name   = $_FILES['cover_buku']['tmp_name'];
    $error_file = $_FILES['cover_buku']['error'];

    if ($error_file === 0) {
        // --- JIKA GAMBAR BARU DIUNGGAH ---
        $ekstensi = pathinfo($nama_cover, PATHINFO_EXTENSION);
        $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array(strtolower($ekstensi), $ekstensi_diizinkan)) {
            // Ambil data cover lama untuk dihapus dari folder
            $query_select = mysqli_query($koneksi, "SELECT cover_buku FROM tbl_buku WHERE id_buku='$id_buku'");
            $data_lama    = mysqli_fetch_object($query_select);
            
            if ($data_lama && !empty($data_lama->cover_buku)) {
                $path_cover_lama = "../../images/" . $data_lama->cover_buku;
                if (file_exists($path_cover_lama)) {
                    unlink($path_cover_lama); // Hapus cover lama dari folder
                }
            }

            // Simpan cover baru ke folder
            $nama_cover_baru = time() . '_' . $nama_cover;
            $folder_tujuan   = '../../images/' . $nama_cover_baru;

            if (move_uploaded_file($tmp_name, $folder_tujuan)) {
                // Query update DENGAN mengubah nama cover_buku
                $query = "UPDATE tbl_buku SET 
                            judul        = '$judul',
                            penulis      = '$penulis',
                            penerbit     = '$penerbit',
                            tahun_terbit = '$tahun_terbit',
                            cover_buku   = '$nama_cover_baru',
                            stok         = '$stok',
                            id_kategori  = '$id_kategori'
                          WHERE id_buku  = '$id_buku'";
            } else {
                echo "<script>
                        alert('Gagal mengunggah cover baru!');
                        window.history.back();
                      </script>";
                exit();
            }
        } else {
            echo "<script>
                    alert('Format gambar tidak valid! Gunakan jpg, jpeg, png, atau webp.');
                    window.history.back();
                  </script>";
            exit();
        }

    } else {
        // --- JIKA GAMBAR TIDAK DIUBAH ---
        // Query update TANPA mengubah kolom cover_buku
        $query = "UPDATE tbl_buku SET 
                    judul        = '$judul',
                    penulis      = '$penulis',
                    penerbit     = '$penerbit',
                    tahun_terbit = '$tahun_terbit',
                    stok         = '$stok',
                    id_kategori  = '$id_kategori'
                  WHERE id_buku  = '$id_buku'";
    }

    // 3. Eksekusi query ke database
    if (mysqli_query($koneksi, $query)) {
        // Redirect bersih ke buku.php (tanpa ?edit=), sehingga $edit_data otomatis NULL dan form menjadi kosong/mode Tambah
        echo "<script>
                alert('Data buku berhasil diperbarui!');
                window.location.href = 'buku.php';
              </script>";
    } else {
        echo "Gagal memperbarui data: " . mysqli_error($koneksi);
    }

} else {
    // Jika diakses tanpa method POST
    header("Location: buku.php");
    exit();
}