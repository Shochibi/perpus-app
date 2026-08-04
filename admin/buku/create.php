<?php
require '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari form (sesuaikan nama field $_POST dari form HTML)
    $judul        = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis      = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $penerbit     = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $stok         = $_POST['stok'];
    $id_kategori  = $_POST['id_kategori']; // Mengambil nilai ID Kategori

    // 2. Proses upload cover_buku
    $nama_cover = $_FILES['cover_buku']['name'];
    $tmp_name   = $_FILES['cover_buku']['tmp_name'];
    $error_file = $_FILES['cover_buku']['error'];

    // Cek apakah ada file yang diunggah
    if ($error_file === 0) {
        // Ambil ekstensi gambar
        $ekstensi = pathinfo($nama_cover, PATHINFO_EXTENSION);
        $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array(strtolower($ekstensi), $ekstensi_diizinkan)) {
            // Ubah nama file agar unik
            $nama_cover_baru = time() . '_' . $nama_cover;
            $folder_tujuan   = '../../images/' . $nama_cover_baru;

            // Pindahkan file dari temporary ke folder images
            if (move_uploaded_file($tmp_name, $folder_tujuan)) {

                // 3. Simpan data ke database sesuai nama kolom baru
                $query = "INSERT INTO tbl_buku (judul, penulis, penerbit, tahun_terbit, cover_buku, stok, id_kategori) 
                          VALUES ('$judul', '$penulis', '$penerbit', '$tahun_terbit', '$nama_cover_baru', '$stok', '$id_kategori')";

                if (mysqli_query($koneksi, $query)) {
                    echo "<script>
                            alert('Data buku berhasil ditambahkan!');
                            window.location.href = 'buku.php';
                          </script>";
                } else {
                    echo "Gagal menambahkan data: " . mysqli_error($koneksi);
                }

            } else {
                echo "<script>
                        alert('Gagal mengunggah gambar ke server!');
                        window.history.back();
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Format gambar tidak valid! Gunakan jpg, jpeg, png, atau webp.');
                    window.history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('Silakan pilih gambar cover terlebih dahulu!');
                window.history.back();
              </script>";
    }
} else {
    // Jika file diakses langsung tanpa method POST
    header("Location: ../index.php?page=buku");
    exit();
}