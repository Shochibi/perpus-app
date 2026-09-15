USE db_lib;

INSERT INTO tbl_buku
(id_kategori, judul_buku, penulis_buku, penerbit_buku, deskripsi, tahun_terbit, cover_buku, pdf_buku, stok)
SELECT * FROM (
    SELECT 1 AS id_kategori, 'Kota yang Menyimpan Hujan' AS judul_buku, 'Nadia Pramesti' AS penulis_buku, 'Pustaka Senja' AS penerbit_buku, 'Novel tentang persahabatan, kehilangan, dan sebuah kota yang selalu mengingat cerita penghuninya.' AS deskripsi, 2023 AS tahun_terbit, 'cover-kota-hujan.svg' AS cover_buku, NULL AS pdf_buku, 4 AS stok UNION ALL
    SELECT 1, 'Peta untuk Pulang', 'Raka Adinata', 'Lentera Aksara', 'Kisah perjalanan seorang anak muda mencari arti rumah di antara jalan-jalan yang belum pernah ia kenal.', 2022, 'cover-peta-pulang.svg', NULL, 3 UNION ALL
    SELECT 1, 'Musim di Balik Jendela', 'Alya Kirana', 'Bintang Buku', 'Kumpulan cerita pendek tentang keluarga, waktu, dan percakapan kecil yang mengubah hidup.', 2021, 'cover-musim-jendela.svg', NULL, 5 UNION ALL
    SELECT 2, 'Logika Digital Sehari-hari', 'Dimas Wirawan', 'Nusa Cipta', 'Pengantar ringan untuk memahami cara kerja teknologi digital yang kita gunakan setiap hari.', 2024, 'cover-logika-digital.svg', NULL, 6 UNION ALL
    SELECT 2, 'Membangun Web dari Nol', 'Sinta Maheswari', 'Kode Terbuka', 'Panduan praktis mengenal HTML, CSS, PHP, dan database melalui proyek perpustakaan sederhana.', 2023, 'cover-web-nol.svg', NULL, 4 UNION ALL
    SELECT 2, 'Data dan Keputusan', 'Fajar Nugraha', 'Ruang Ilmu', 'Cara membaca data dengan jernih dan mengubah angka menjadi keputusan yang lebih baik.', 2022, 'cover-data-keputusan.svg', NULL, 3 UNION ALL
    SELECT 2, 'Jejak Algoritma', 'Bima Saputra', 'Kompas Tekno', 'Pengenalan algoritma dan pemecahan masalah untuk pembaca yang baru mulai belajar pemrograman.', 2021, 'cover-jejak-algoritma.svg', NULL, 5 UNION ALL
    SELECT 3, 'Matematika yang Masuk Akal', 'Maya Lestari', 'Cakrawala Edu', 'Konsep matematika dijelaskan dengan contoh dekat dengan kehidupan sehari-hari.', 2024, 'cover-matematika.svg', NULL, 7 UNION ALL
    SELECT 3, 'Sains di Sekitar Kita', 'Arif Rahman', 'Pelangi Pendidikan', 'Eksperimen dan penjelasan singkat tentang fenomena sains yang dapat diamati di rumah.', 2023, 'cover-sains.svg', NULL, 5 UNION ALL
    SELECT 3, 'Belajar Efektif', 'Niken Wulandari', 'Tumbuh Media', 'Strategi belajar, mencatat, dan mengatur waktu untuk pelajar yang ingin berkembang konsisten.', 2022, 'cover-belajar-efektif.svg', NULL, 4 UNION ALL
    SELECT 3, 'Atlas Tata Surya', 'Tio Prakoso', 'Langit Biru', 'Perjalanan visual mengenal planet, bintang, dan benda langit di sekitar tata surya.', 2020, 'cover-tata-surya.svg', NULL, 2 UNION ALL
    SELECT 4, 'Bahasa dalam Percakapan', 'Larasati Putri', 'Kata Kita', 'Panduan menggunakan bahasa Indonesia yang jelas, santun, dan sesuai konteks.', 2023, 'cover-bahasa.svg', NULL, 4 UNION ALL
    SELECT 4, 'Menulis dengan Suara Sendiri', 'Yusuf Maulana', 'Aksara Rumah', 'Latihan menemukan gaya menulis dan menyusun gagasan menjadi tulisan yang hidup.', 2022, 'cover-menulis.svg', NULL, 3 UNION ALL
    SELECT 4, 'Kamus Mini Nusantara', 'Dewi Anjani', 'Balai Kata', 'Kumpulan kosakata pilihan dari berbagai daerah di Indonesia beserta maknanya.', 2021, 'cover-kamus-nusantara.svg', NULL, 3 UNION ALL
    SELECT 5, 'Kebun Kecil di Rumah', 'Rani Puspita', 'Hijau Lestari', 'Panduan praktis menanam sayur dan rempah di ruang terbatas.', 2024, 'cover-kebun.svg', NULL, 6 UNION ALL
    SELECT 5, 'Rasa dari Dapur Ibu', 'Sari Handayani', 'Dapur Kita', 'Resep rumahan sederhana yang membawa kembali kehangatan meja makan keluarga.', 2020, 'cover-dapur.svg', NULL, 4 UNION ALL
    SELECT 5, 'Catatan Perjalanan Jawa', 'Galih Pranoto', 'Jelajah Nusantara', 'Catatan perjalanan tentang lanskap, kuliner, dan perjumpaan kecil di sepanjang Pulau Jawa.', 2022, 'cover-perjalanan-jawa.svg', NULL, 3 UNION ALL
    SELECT 5, 'Hidup Lebih Teratur', 'Mira Anggraini', 'Ruang Seimbang', 'Gagasan sederhana untuk merapikan rutinitas, ruang, dan prioritas tanpa terburu-buru.', 2023, 'cover-teratur.svg', NULL, 5
) AS buku_baru
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_buku b WHERE b.judul_buku = buku_baru.judul_buku
);
