USE db_lib;

INSERT INTO tbl_buku
(id_kategori, judul_buku, penulis_buku, penerbit_buku, deskripsi, tahun_terbit, cover_buku, pdf_buku, stok)
SELECT new_books.*
FROM (
    SELECT 1 AS id_kategori, 'Perahu Kertas' AS judul_buku, 'Dee Lestari' AS penulis_buku, 'Bentang Pustaka' AS penerbit_buku, 'Perjalanan cinta, mimpi, dan pencarian diri dua anak muda.' AS deskripsi, 2009 AS tahun_terbit, 'real-24.jpg' AS cover_buku, NULL AS pdf_buku, 4 AS stok
    UNION ALL SELECT 1, 'Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia Pustaka Utama', 'Perjalanan pendidikan dan persahabatan para santri dalam mengejar cita-cita.', 2009, 'real-25.jpg', NULL, 4
    UNION ALL SELECT 1, 'Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', 'Republika', 'Kisah cinta, iman, dan keteguhan hati dalam menghadapi pilihan hidup.', 2004, 'real-26.jpg', NULL, 3
    UNION ALL SELECT 1, 'Komet Minor', 'Tere Liye', 'Gramedia Pustaka Utama', 'Petualangan fantasi tentang keberanian, persahabatan, dan pilihan hidup.', 2019, 'real-23.jpg', NULL, 4

    UNION ALL SELECT 2, 'Introduction to Algorithms', 'Thomas H. Cormen et al.', 'MIT Press', 'Pengantar komprehensif untuk algoritma dan pemecahan masalah komputasi.', 2022, 'real-27.jpg', NULL, 3
    UNION ALL SELECT 2, 'Designing Data-Intensive Applications', 'Martin Kleppmann', 'O’Reilly Media', 'Gagasan penting tentang sistem data yang andal dan mudah dikembangkan.', 2017, 'real-28.jpg', NULL, 3
    UNION ALL SELECT 2, 'You Don’t Know JS Yet', 'Kyle Simpson', 'O’Reilly Media', 'Eksplorasi mendalam dan praktis tentang bahasa JavaScript.', 2020, 'real-29.jpg', NULL, 4
    UNION ALL SELECT 2, 'The Clean Coder', 'Robert C. Martin', 'Prentice Hall', 'Panduan profesionalisme, disiplin, dan praktik kerja bagi pengembang.', 2011, 'real-08.jpg', NULL, 3

    UNION ALL SELECT 3, 'A Short History of Nearly Everything', 'Bill Bryson', 'Broadway Books', 'Tur singkat dan menarik tentang penemuan yang menjelaskan dunia kita.', 2003, 'real-30.jpg', NULL, 4
    UNION ALL SELECT 3, 'The Selfish Gene', 'Richard Dawkins', 'Oxford University Press', 'Penjelasan evolusi dari sudut pandang gen dan seleksi alam.', 1976, 'real-31.jpg', NULL, 3
    UNION ALL SELECT 3, 'The Gene', 'Siddhartha Mukherjee', 'Scribner', 'Sejarah dan masa depan salah satu gagasan besar dalam biologi.', 2016, 'real-32.jpg', NULL, 4
    UNION ALL SELECT 3, 'The Language Instinct', 'Steven Pinker', 'William Morrow', 'Pengantar ilmu bahasa dan cara manusia menggunakan bahasa.', 1994, 'real-34.jpg', NULL, 3

    UNION ALL SELECT 4, 'The Professor and the Madman', 'Simon Winchester', 'Harper Perennial', 'Kisah di balik penyusunan Oxford English Dictionary.', 1998, 'real-35.jpg', NULL, 3
    UNION ALL SELECT 4, 'The Poetry Handbook', 'Mary Oliver', 'Mariner Books', 'Panduan membaca dan menulis puisi dengan cara yang mendalam.', 1994, 'real-36.jpg', NULL, 4
    UNION ALL SELECT 4, 'The Elements of Style Workbook', 'William Strunk Jr. dan E. B. White', 'Pearson', 'Latihan menulis untuk memperjelas kalimat dan menyusun gagasan.', 2001, 'real-16.jpg', NULL, 3
    UNION ALL SELECT 4, 'Writing with Style', 'Steven Pinker', 'Viking', 'Panduan menyampaikan gagasan secara jelas, ringkas, dan menarik.', 2015, 'real-18.jpg', NULL, 3

    UNION ALL SELECT 5, 'Educated', 'Tara Westover', 'Random House', 'Memoar tentang pendidikan, keluarga, dan perjuangan membangun hidup baru.', 2018, 'real-37.jpg', NULL, 4
    UNION ALL SELECT 5, 'The Power of Now', 'Eckhart Tolle', 'New World Library', 'Panduan kesadaran diri dan hidup lebih penuh di masa kini.', 1999, 'real-38.jpg', NULL, 4
    UNION ALL SELECT 5, 'Rich Dad Poor Dad', 'Robert T. Kiyosaki', 'Plata Publishing', 'Pelajaran keuangan pribadi tentang uang, kerja, dan kemandirian.', 1997, 'real-39.jpg', NULL, 3
    UNION ALL SELECT 5, 'The 7 Habits Workbook', 'Stephen R. Covey', 'Simon and Schuster', 'Latihan membangun kebiasaan efektif untuk kehidupan sehari-hari.', 2000, 'real-21.jpg', NULL, 3
) AS new_books
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_buku existing_book WHERE existing_book.judul_buku = new_books.judul_buku
);
