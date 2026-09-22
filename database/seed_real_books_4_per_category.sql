USE db_lib;

INSERT INTO tbl_buku
(id_kategori, judul_buku, penulis_buku, penerbit_buku, deskripsi, tahun_terbit, cover_buku, pdf_buku, stok)
SELECT new_books.*
FROM (
    SELECT 1 AS id_kategori, 'The Great Gatsby' AS judul_buku, 'F. Scott Fitzgerald' AS penulis_buku, 'Scribner' AS penerbit_buku, 'Novel klasik tentang ambisi, identitas, dan impian Amerika.' AS deskripsi, 1925 AS tahun_terbit, 'real-05.jpg' AS cover_buku, NULL AS pdf_buku, 4 AS stok
    UNION ALL SELECT 1, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 'Kisah persahabatan, pendidikan, dan perjuangan anak-anak Belitung.', 2005, 'real-06.jpg', NULL, 5
    UNION ALL SELECT 1, 'Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 'Novel sejarah tentang pendidikan, cinta, dan ketidakadilan kolonial.', 1980, 'real-07.jpg', NULL, 3
    UNION ALL SELECT 1, 'Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', 'Perjalanan tiga sahabat mengejar pendidikan dan cita-cita.', 2006, 'real-23.jpg', NULL, 4

    UNION ALL SELECT 2, 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 'Panduan menulis kode yang bersih, mudah dibaca, dan mudah dirawat.', 2008, 'real-08.jpg', NULL, 4
    UNION ALL SELECT 2, 'The Pragmatic Programmer', 'David Thomas dan Andrew Hunt', 'Addison-Wesley', 'Pelajaran praktis untuk menjadi pengembang perangkat lunak yang lebih baik.', 2019, 'real-09.jpg', NULL, 4
    UNION ALL SELECT 2, 'Eloquent JavaScript', 'Marijn Haverbeke', 'No Starch Press', 'Pengantar JavaScript, pemrograman, dan pengembangan web modern.', 2018, 'real-10.jpg', NULL, 3
    UNION ALL SELECT 2, 'Don’t Make Me Think', 'Steve Krug', 'New Riders', 'Panduan membuat antarmuka web yang sederhana dan mudah digunakan.', 2014, 'real-11.jpg', NULL, 5

    UNION ALL SELECT 3, 'A Brief History of Time', 'Stephen Hawking', 'Bantam', 'Perjalanan memahami asal-usul dan sifat alam semesta.', 1988, 'real-12.jpg', NULL, 3
    UNION ALL SELECT 3, 'Cosmos', 'Carl Sagan', 'Random House', 'Eksplorasi sains, alam semesta, dan tempat manusia di dalamnya.', 1980, 'real-13.jpg', NULL, 4
    UNION ALL SELECT 3, 'Sapiens', 'Yuval Noah Harari', 'Harper', 'Sejarah singkat manusia dari zaman batu hingga masa modern.', 2015, 'real-14.jpg', NULL, 4
    UNION ALL SELECT 3, 'Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar, Straus and Giroux', 'Penjelasan tentang dua sistem yang memengaruhi penilaian dan keputusan manusia.', 2011, 'real-15.jpg', NULL, 3

    UNION ALL SELECT 4, 'The Elements of Style', 'William Strunk Jr. dan E. B. White', 'Pearson', 'Panduan ringkas menulis bahasa Inggris dengan jelas dan efektif.', 2000, 'real-16.jpg', NULL, 3
    UNION ALL SELECT 4, 'On Writing Well', 'William Zinsser', 'Harper Perennial', 'Panduan klasik menulis nonfiksi dengan jelas dan sederhana.', 2006, 'real-17.jpg', NULL, 4
    UNION ALL SELECT 4, 'The Sense of Style', 'Steven Pinker', 'Viking', 'Panduan modern menulis dengan baik pada abad ke-21.', 2014, 'real-18.jpg', NULL, 3
    UNION ALL SELECT 4, 'Eats, Shoots & Leaves', 'Lynne Truss', 'Gotham Books', 'Panduan ringan tentang tanda baca dan pentingnya ketepatan bahasa.', 2004, 'real-33.jpg', NULL, 4

    UNION ALL SELECT 5, 'Atomic Habits', 'James Clear', 'Avery', 'Cara praktis membangun kebiasaan baik dan mengubah rutinitas.', 2018, 'real-19.jpg', NULL, 5
    UNION ALL SELECT 5, 'How to Win Friends and Influence People', 'Dale Carnegie', 'Simon and Schuster', 'Prinsip komunikasi dan hubungan sosial yang tetap relevan.', 1936, 'real-20.jpg', NULL, 4
    UNION ALL SELECT 5, 'The 7 Habits of Highly Effective People', 'Stephen R. Covey', 'Simon and Schuster', 'Pendekatan berbasis prinsip untuk efektivitas pribadi dan profesional.', 1989, 'real-21.jpg', NULL, 3
    UNION ALL SELECT 5, 'Ikigai', 'Hector Garcia dan Francesc Miralles', 'Penguin Life', 'Panduan menemukan tujuan hidup dan makna dalam keseharian.', 2017, 'real-22.jpg', NULL, 4
) AS new_books
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_buku existing_book WHERE existing_book.judul_buku = new_books.judul_buku
);
