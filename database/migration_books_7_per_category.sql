USE db_lib;

INSERT INTO tbl_buku
(id_kategori, judul_buku, penulis_buku, penerbit_buku, deskripsi, tahun_terbit, cover_buku, pdf_buku, stok)
SELECT new_books.*
FROM (
    SELECT 1 AS id_kategori, 'Rumah di Ujung Senja' AS judul_buku, 'Nara Wijaya' AS penulis_buku, 'Pustaka Senja' AS penerbit_buku, 'Cerita tentang keluarga yang menemukan harapan dari rumah lama di tepi kota.' AS deskripsi, 2020 AS tahun_terbit, NULL AS cover_buku, NULL AS pdf_buku, 4 AS stok
    UNION ALL SELECT 1, 'Surat yang Tak Pernah Terkirim', 'Dian Lestari', 'Aksara Hati', 'Novel tentang rahasia, perpisahan, dan keberanian untuk memulai kembali.', 2021, NULL, NULL, 3
    UNION ALL SELECT 1, 'Langit Setelah Hujan', 'Bintang Pratama', 'Buku Kita', 'Kisah persahabatan yang tumbuh setelah sebuah kehilangan besar.', 2022, NULL, NULL, 5
    UNION ALL SELECT 1, 'Taman Rahasia Kota', 'Mira Anjani', 'Ruang Cerita', 'Petualangan ringan menemukan taman tersembunyi dan cerita penghuninya.', 2024, NULL, NULL, 4
    UNION ALL SELECT 2, 'Pemrograman untuk Pemula', 'Andika Putra', 'Kode Terbuka', 'Dasar pemrograman dengan contoh sederhana untuk pembaca baru.', 2024, NULL, NULL, 5
    UNION ALL SELECT 2, 'Mengenal Keamanan Digital', 'Rizky Ramadhan', 'Nusa Cipta', 'Panduan menjaga akun, data pribadi, dan aktivitas digital sehari-hari.', 2023, NULL, NULL, 4
    UNION ALL SELECT 2, 'Desain Aplikasi Modern', 'Salsa Maharani', 'Kompas Tekno', 'Prinsip dasar merancang aplikasi yang mudah dipahami dan nyaman digunakan.', 2022, NULL, NULL, 3
    UNION ALL SELECT 3, 'Fisika Tanpa Rumus Rumit', 'Raka Firmansyah', 'Cakrawala Edu', 'Penjelasan konsep fisika melalui kejadian yang dekat dengan kehidupan.', 2023, NULL, NULL, 6
    UNION ALL SELECT 3, 'Biologi untuk Kehidupan', 'Putri Amalia', 'Pelangi Pendidikan', 'Mengenal makhluk hidup dan hubungan mereka dengan lingkungan.', 2022, NULL, NULL, 5
    UNION ALL SELECT 3, 'Sejarah Indonesia Ringkas', 'Bagas Santoso', 'Tumbuh Media', 'Ringkasan peristiwa penting dalam sejarah Indonesia dengan bahasa sederhana.', 2021, NULL, NULL, 4
    UNION ALL SELECT 4, 'Menjadi Pembicara yang Jelas', 'Ayu Permata', 'Kata Kita', 'Latihan berbicara dengan percaya diri dalam percakapan dan presentasi.', 2024, NULL, NULL, 4
    UNION ALL SELECT 4, 'Cerita dari Kata-Kata', 'Fikri Hadi', 'Aksara Rumah', 'Panduan menyusun kalimat dan cerita yang menarik untuk berbagai pembaca.', 2023, NULL, NULL, 3
    UNION ALL SELECT 4, 'Panduan Ejaan Praktis', 'Maya Sari', 'Balai Kata', 'Rangkuman ejaan dan tanda baca untuk tulisan sehari-hari.', 2022, NULL, NULL, 5
    UNION ALL SELECT 4, 'Puisi di Tengah Kota', 'Nina Kencana', 'Kata Kita', 'Kumpulan puisi tentang kota, manusia, dan percakapan yang sederhana.', 2020, NULL, NULL, 2
    UNION ALL SELECT 5, 'Merawat Tanaman Hias', 'Rendra Kusuma', 'Hijau Lestari', 'Panduan memilih dan merawat tanaman hias di dalam maupun luar rumah.', 2024, NULL, NULL, 5
    UNION ALL SELECT 5, 'Jalan-Jalan Kuliner Nusantara', 'Citra Wulandari', 'Jelajah Nusantara', 'Catatan rasa dan cerita dari berbagai makanan khas Indonesia.', 2023, NULL, NULL, 4
    UNION ALL SELECT 5, 'Kebiasaan Kecil yang Baik', 'Dewi Laras', 'Ruang Seimbang', 'Ide sederhana membangun rutinitas yang lebih sehat dan teratur.', 2021, NULL, NULL, 6
) AS new_books
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_buku existing_book WHERE existing_book.judul_buku = new_books.judul_buku
);
