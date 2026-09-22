USE db_lib;

INSERT INTO tbl_kategori (nama_kategori)
SELECT nama_kategori
FROM (
    SELECT 'Agama' AS nama_kategori
    UNION ALL SELECT 'Sejarah'
    UNION ALL SELECT 'Biografi'
    UNION ALL SELECT 'Psikologi'
    UNION ALL SELECT 'Bisnis'
    UNION ALL SELECT 'Anak-anak'
    UNION ALL SELECT 'Komik'
) AS new_categories
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_kategori existing_category
    WHERE existing_category.nama_kategori = new_categories.nama_kategori
);

INSERT INTO tbl_buku
(id_kategori, judul_buku, penulis_buku, penerbit_buku, deskripsi, tahun_terbit, cover_buku, pdf_buku, stok)
SELECT k.id_kategori, new_books.judul_buku, new_books.penulis_buku, new_books.penerbit_buku, new_books.deskripsi, new_books.tahun_terbit, new_books.cover_buku, NULL, new_books.stok
FROM tbl_kategori k
JOIN (
    SELECT 'Agama' AS nama_kategori, 'The Road to Mecca' AS judul_buku, 'Muhammad Asad' AS penulis_buku, 'Islamic Book Trust' AS penerbit_buku, 'Catatan perjalanan spiritual dan pencarian makna kehidupan.' AS deskripsi, 1954 AS tahun_terbit, 'real-12.jpg' AS cover_buku, 4 AS stok
    UNION ALL SELECT 'Agama', 'The Prophet', 'Kahlil Gibran', 'Alfred A. Knopf', 'Renungan tentang cinta, kebebasan, kerja, dan kehidupan manusia.', 1923, 'real-13.jpg', 3
    UNION ALL SELECT 'Agama', 'Man’s Search for Meaning', 'Viktor E. Frankl', 'Beacon Press', 'Refleksi tentang makna hidup dan keteguhan menghadapi kesulitan.', 1946, 'real-39.jpg', 4
    UNION ALL SELECT 'Agama', 'The Art of Happiness', 'Dalai Lama dan Howard Cutler', 'Riverhead Books', 'Panduan membangun ketenangan dan kebahagiaan dalam kehidupan sehari-hari.', 1998, 'real-38.jpg', 3
    UNION ALL SELECT 'Agama', 'The Book of Joy', 'Dalai Lama dan Desmond Tutu', 'Avery', 'Percakapan tentang sukacita, ketabahan, dan kehidupan yang bermakna.', 2016, 'real-37.jpg', 3

    UNION ALL SELECT 'Sejarah', 'Guns, Germs, and Steel', 'Jared Diamond', 'W. W. Norton', 'Penjelasan tentang perkembangan masyarakat dan peradaban dunia.', 1997, 'real-14.jpg', 4
    UNION ALL SELECT 'Sejarah', 'The Silk Roads', 'Peter Frankopan', 'Bloomsbury', 'Sejarah dunia melalui jalur perdagangan dan pertukaran budaya.', 2015, 'real-15.jpg', 3
    UNION ALL SELECT 'Sejarah', 'A History of the World', 'Andrew Marr', 'Macmillan', 'Perjalanan ringkas melihat perubahan besar dalam sejarah dunia.', 2012, 'real-30.jpg', 4
    UNION ALL SELECT 'Sejarah', 'Indonesia dalam Arus Sejarah', 'Taufik Abdullah', 'Ichtiar Baru', 'Gambaran perjalanan bangsa Indonesia dari masa ke masa.', 2018, 'real-06.jpg', 3

    UNION ALL SELECT 'Biografi', 'Long Walk to Freedom', 'Nelson Mandela', 'Little, Brown', 'Memoar perjuangan, kepemimpinan, dan kebebasan.', 1994, 'real-20.jpg', 4
    UNION ALL SELECT 'Biografi', 'Steve Jobs', 'Walter Isaacson', 'Simon and Schuster', 'Biografi tentang visi, inovasi, dan kehidupan Steve Jobs.', 2011, 'real-21.jpg', 3
    UNION ALL SELECT 'Biografi', 'Becoming', 'Michelle Obama', 'Crown', 'Kisah perjalanan hidup, keluarga, dan pelayanan publik.', 2018, 'real-37.jpg', 4
    UNION ALL SELECT 'Biografi', 'Einstein: His Life and Universe', 'Walter Isaacson', 'Simon and Schuster', 'Kehidupan dan gagasan ilmiah Albert Einstein.', 2007, 'real-12.jpg', 3

    UNION ALL SELECT 'Psikologi', 'Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar, Straus and Giroux', 'Memahami cara manusia berpikir dan mengambil keputusan.', 2011, 'real-15.jpg', 4
    UNION ALL SELECT 'Psikologi', 'Influence', 'Robert B. Cialdini', 'Harper Business', 'Prinsip psikologi di balik pengaruh dan persuasi.', 2006, 'real-19.jpg', 3
    UNION ALL SELECT 'Psikologi', 'The Psychology of Money', 'Morgan Housel', 'Harriman House', 'Pelajaran perilaku manusia dalam mengambil keputusan finansial.', 2020, 'real-22.jpg', 4
    UNION ALL SELECT 'Psikologi', 'Quiet', 'Susan Cain', 'Crown', 'Kekuatan dan potensi orang-orang introvert dalam dunia modern.', 2012, 'real-18.jpg', 3
    UNION ALL SELECT 'Psikologi', 'Emotional Intelligence', 'Daniel Goleman', 'Bantam Books', 'Panduan memahami emosi, hubungan sosial, dan kecerdasan diri.', 1995, 'real-36.jpg', 4

    UNION ALL SELECT 'Bisnis', 'The Lean Startup', 'Eric Ries', 'Crown Business', 'Metode membangun bisnis dengan eksperimen dan pembelajaran cepat.', 2011, 'real-28.jpg', 4
    UNION ALL SELECT 'Bisnis', 'Good to Great', 'Jim Collins', 'HarperBusiness', 'Prinsip yang membantu organisasi berkembang secara konsisten.', 2001, 'real-20.jpg', 3
    UNION ALL SELECT 'Bisnis', 'Zero to One', 'Peter Thiel', 'Crown Business', 'Gagasan membangun inovasi dan bisnis yang berbeda.', 2014, 'real-29.jpg', 4
    UNION ALL SELECT 'Bisnis', 'The Intelligent Investor', 'Benjamin Graham', 'HarperBusiness', 'Dasar pemikiran investasi jangka panjang yang disiplin.', 1949, 'real-21.jpg', 3

    UNION ALL SELECT 'Anak-anak', 'The Little Prince', 'Antoine de Saint-Exupéry', 'Reynal and Hitchcock', 'Cerita sederhana tentang persahabatan, cinta, dan cara melihat dunia.', 1943, 'real-23.jpg', 4
    UNION ALL SELECT 'Anak-anak', 'Charlotte’s Web', 'E. B. White', 'HarperCollins', 'Kisah persahabatan antara seekor laba-laba dan seekor babi kecil.', 1952, 'real-24.jpg', 3
    UNION ALL SELECT 'Anak-anak', 'Matilda', 'Roald Dahl', 'Jonathan Cape', 'Petualangan anak cerdas yang menemukan keberanian dan kekuatannya.', 1988, 'real-25.jpg', 4
    UNION ALL SELECT 'Anak-anak', 'The Wonderful Wizard of Oz', 'L. Frank Baum', 'George M. Hill', 'Petualangan penuh imajinasi menuju negeri Oz.', 1900, 'real-26.jpg', 3

    UNION ALL SELECT 'Komik', 'The Complete Peanuts', 'Charles M. Schulz', 'Fantagraphics', 'Kumpulan kisah ringan dan hangat dari Charlie Brown dan teman-temannya.', 2004, 'real-33.jpg', 4
    UNION ALL SELECT 'Komik', 'The Sandman', 'Neil Gaiman', 'DC Comics', 'Fantasi mitologis tentang mimpi dan dunia yang tak biasa.', 1989, 'real-34.jpg', 3
    UNION ALL SELECT 'Komik', 'Maus', 'Art Spiegelman', 'Pantheon Books', 'Novel grafis tentang sejarah, keluarga, dan ingatan.', 1991, 'real-35.jpg', 4
    UNION ALL SELECT 'Komik', 'Persepolis', 'Marjane Satrapi', 'Pantheon Books', 'Memoar grafis tentang masa kecil, perubahan, dan identitas.', 2000, 'real-36.jpg', 3
) AS new_books ON new_books.nama_kategori = k.nama_kategori
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_buku existing_book
    WHERE existing_book.judul_buku = new_books.judul_buku
);
