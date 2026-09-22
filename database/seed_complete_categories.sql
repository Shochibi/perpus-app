USE db_lib;

INSERT INTO tbl_buku
(id_kategori, judul_buku, penulis_buku, penerbit_buku, deskripsi, tahun_terbit, cover_buku, pdf_buku, stok)
SELECT new_books.*
FROM (
    SELECT 1 AS id_kategori, 'Rindu yang Berlabuh' AS judul_buku, 'Tere Liye' AS penulis_buku, 'Republika' AS penerbit_buku, 'Kisah perjalanan, keluarga, dan harapan yang tumbuh dari perpisahan.', 2015 AS tahun_terbit, 'real-24.jpg' AS cover_buku, NULL AS pdf_buku, 4 AS stok
    UNION ALL SELECT 1, 'Hujan di Balik Senyum', 'Andrea Hirata', 'Bentang Pustaka', 'Cerita tentang persahabatan dan keberanian menerima perubahan hidup.', 2016, 'real-23.jpg', NULL, 3
    UNION ALL SELECT 1, 'Pulang', 'Leila S. Chudori', 'Kepustakaan Populer Gramedia', 'Novel tentang kehilangan, identitas, dan arti pulang bagi sebuah keluarga.', 2012, 'real-25.jpg', NULL, 4

    UNION ALL SELECT 2, 'Refactoring', 'Martin Fowler', 'Addison-Wesley', 'Panduan memperbaiki struktur kode tanpa mengubah perilaku program.', 2018, 'real-28.jpg', NULL, 3
    UNION ALL SELECT 2, 'Head First Design Patterns', 'Eric Freeman', 'O’Reilly Media', 'Pengenalan pola desain perangkat lunak melalui contoh yang mudah dipahami.', 2020, 'real-29.jpg', NULL, 4
    UNION ALL SELECT 2, 'Computer Networking', 'Andrew S. Tanenbaum', 'Pearson', 'Dasar jaringan komputer, protokol, dan komunikasi data modern.', 2019, 'real-27.jpg', NULL, 3

    UNION ALL SELECT 3, 'The Origin of Species', 'Charles Darwin', 'Penguin Classics', 'Karya penting tentang evolusi dan keragaman makhluk hidup.', 1859, 'real-31.jpg', NULL, 3
    UNION ALL SELECT 3, 'The Immortal Life of Henrietta Lacks', 'Rebecca Skloot', 'Crown Publishing', 'Kisah sains, etika, dan kehidupan di balik penemuan medis penting.', 2010, 'real-32.jpg', NULL, 4
    UNION ALL SELECT 3, 'Silent Spring', 'Rachel Carson', 'Houghton Mifflin', 'Kajian tentang lingkungan dan dampak bahan kimia terhadap alam.', 1962, 'real-30.jpg', NULL, 3
    UNION ALL SELECT 3, 'The Story of Art', 'E. H. Gombrich', 'Phaidon', 'Perjalanan ringkas memahami perkembangan seni dari masa ke masa.', 1950, 'real-13.jpg', NULL, 3

    UNION ALL SELECT 4, 'The Oxford Guide to Writing', 'Thomas S. Kane', 'Oxford University Press', 'Panduan menyusun tulisan yang jelas, teratur, dan efektif.', 2000, 'real-16.jpg', NULL, 3
    UNION ALL SELECT 4, 'Merriam-Webster Dictionary', 'Merriam-Webster', 'Merriam-Webster', 'Referensi kosakata dan makna kata untuk pembaca umum.', 2020, 'real-17.jpg', NULL, 4

    UNION ALL SELECT 5, 'The Subtle Art of Not Giving a F*ck', 'Mark Manson', 'HarperOne', 'Pandangan jujur tentang nilai hidup, pilihan, dan ketahanan diri.', 2016, 'real-38.jpg', NULL, 3
    UNION ALL SELECT 5, 'Man’s Search for Meaning', 'Viktor E. Frankl', 'Beacon Press', 'Renungan tentang makna hidup dan keteguhan manusia menghadapi kesulitan.', 1946, 'real-39.jpg', NULL, 4
    UNION ALL SELECT 5, 'Essentialism', 'Greg McKeown', 'Crown Business', 'Cara memilih hal penting dan menjalani hidup dengan lebih fokus.', 2014, 'real-20.jpg', NULL, 3
) AS new_books
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_buku existing_book WHERE existing_book.judul_buku = new_books.judul_buku
);
