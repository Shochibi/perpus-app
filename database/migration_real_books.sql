USE db_lib;

UPDATE tbl_buku b
JOIN (
    SELECT 5 AS id_buku, 'The Great Gatsby' AS judul_buku, 'F. Scott Fitzgerald' AS penulis_buku, 'Scribner' AS penerbit_buku, 'A classic novel about ambition, identity, and the American dream.' AS deskripsi, 1925 AS tahun_terbit, 'real-05.jpg' AS cover_buku, '9780743273565' AS isbn
    UNION ALL SELECT 6, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 'Novel Indonesia tentang persahabatan, pendidikan, dan perjuangan anak-anak Belitung.', 2005, 'real-06.jpg', '9789793062792'
    UNION ALL SELECT 7, 'Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 'Novel sejarah tentang Minke, pendidikan, cinta, dan ketidakadilan kolonial.', 1980, 'real-07.jpg', '9789799731234'
    UNION ALL SELECT 23, 'Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', 'Kisah persahabatan dan mimpi tiga anak untuk menempuh pendidikan tinggi.', 2006, 'real-23.jpg', '9789793062822'
    UNION ALL SELECT 24, 'Perahu Kertas', 'Dee Lestari', 'Bentang Pustaka', 'Kisah Kugy dan Keenan dalam perjalanan cinta, mimpi, dan pencarian diri.', 2009, 'real-24.jpg', '9786022916626'
    UNION ALL SELECT 25, 'Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia Pustaka Utama', 'Perjalanan pendidikan dan persahabatan para santri yang berani mengejar cita-cita.', 2009, 'real-25.jpg', '9789792248616'
    UNION ALL SELECT 26, 'Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', 'Republika', 'Novel tentang cinta, iman, dan keteguhan hati dalam menghadapi pilihan hidup.', 2004, 'real-26.jpg', '9789793604501'
    UNION ALL SELECT 8, 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 'A practical guide to writing readable, maintainable, and professional software code.', 2008, 'real-08.jpg', '9780132350884'
    UNION ALL SELECT 9, 'The Pragmatic Programmer', 'David Thomas and Andrew Hunt', 'Addison-Wesley', 'Practical lessons for becoming a better software developer and problem solver.', 2019, 'real-09.jpg', '9780135957059'
    UNION ALL SELECT 10, 'Eloquent JavaScript', 'Marijn Haverbeke', 'No Starch Press', 'A modern introduction to JavaScript, programming, and web development.', 2018, 'real-10.jpg', '9781593279509'
    UNION ALL SELECT 11, 'Don’t Make Me Think', 'Steve Krug', 'New Riders', 'A practical guide to web usability and intuitive interface design.', 2014, 'real-11.jpg', '9780321344755'
    UNION ALL SELECT 27, 'Introduction to Algorithms', 'Thomas H. Cormen et al.', 'MIT Press', 'A comprehensive introduction to algorithms and computational problem solving.', 2022, 'real-27.jpg', '9780262046305'
    UNION ALL SELECT 28, 'Designing Data-Intensive Applications', 'Martin Kleppmann', 'O’Reilly Media', 'The big ideas behind reliable, scalable, and maintainable data systems.', 2017, 'real-28.jpg', '9781449373320'
    UNION ALL SELECT 29, 'You Don’t Know JS Yet', 'Kyle Simpson', 'O’Reilly Media', 'A deep and practical exploration of the JavaScript language.', 2020, 'real-29.jpg', '9781491904244'
    UNION ALL SELECT 12, 'A Brief History of Time', 'Stephen Hawking', 'Bantam', 'An accessible journey through the origin and nature of the universe.', 1988, 'real-12.jpg', '9780553380163'
    UNION ALL SELECT 13, 'Cosmos', 'Carl Sagan', 'Random House', 'A wide-ranging exploration of science, the universe, and humanity’s place in it.', 1980, 'real-13.jpg', '9780345331359'
    UNION ALL SELECT 14, 'Sapiens', 'Yuval Noah Harari', 'Harper', 'A brief history of humankind from the Stone Age to the modern era.', 2015, 'real-14.jpg', '9780062316097'
    UNION ALL SELECT 15, 'Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar, Straus and Giroux', 'An exploration of the two systems that shape human judgment and decisions.', 2011, 'real-15.jpg', '9780374533557'
    UNION ALL SELECT 30, 'A Short History of Nearly Everything', 'Bill Bryson', 'Broadway Books', 'A lively tour through the discoveries that explain our world.', 2003, 'real-30.jpg', '9780767908184'
    UNION ALL SELECT 31, 'The Selfish Gene', 'Richard Dawkins', 'Oxford University Press', 'A landmark explanation of evolution from the perspective of genes.', 1976, 'real-31.jpg', '9780198788607'
    UNION ALL SELECT 32, 'The Gene', 'Siddhartha Mukherjee', 'Scribner', 'The history and future of one of the most powerful ideas in biology.', 2016, 'real-32.jpg', '9781476733500'
    UNION ALL SELECT 16, 'The Elements of Style', 'William Strunk Jr. and E. B. White', 'Pearson', 'A concise guide to clear and effective English writing.', 2000, 'real-16.jpg', '9780205309023'
    UNION ALL SELECT 17, 'On Writing Well', 'William Zinsser', 'Harper Perennial', 'A classic guide to writing nonfiction with clarity and simplicity.', 2006, 'real-17.jpg', '9780060891541'
    UNION ALL SELECT 18, 'The Sense of Style', 'Steven Pinker', 'Viking', 'A modern guide to writing in the twenty-first century.', 2014, 'real-18.jpg', '9780804125758'
    UNION ALL SELECT 33, 'Eats, Shoots & Leaves', 'Lynne Truss', 'Gotham Books', 'A witty guide to punctuation and the importance of precise writing.', 2004, 'real-33.jpg', '9781592402032'
    UNION ALL SELECT 34, 'The Language Instinct', 'Steven Pinker', 'William Morrow', 'An introduction to the science of language and how humans use it.', 1994, 'real-34.jpg', '9780060958336'
    UNION ALL SELECT 35, 'The Professor and the Madman', 'Simon Winchester', 'Harper Perennial', 'The remarkable story behind the Oxford English Dictionary.', 1998, 'real-35.jpg', '9780060837564'
    UNION ALL SELECT 36, 'The Poetry Handbook', 'Mary Oliver', 'Mariner Books', 'A practical and thoughtful guide to reading and writing poetry.', 1994, 'real-36.jpg', '9780393344039'
    UNION ALL SELECT 19, 'Atomic Habits', 'James Clear', 'Avery', 'An easy and proven way to build good habits and break bad ones.', 2018, 'real-19.jpg', '9780735211292'
    UNION ALL SELECT 20, 'How to Win Friends and Influence People', 'Dale Carnegie', 'Simon and Schuster', 'Timeless principles for communication and better relationships.', 1936, 'real-20.jpg', '9780671027032'
    UNION ALL SELECT 21, 'The 7 Habits of Highly Effective People', 'Stephen R. Covey', 'Simon and Schuster', 'A principle-centered approach to personal and professional effectiveness.', 1989, 'real-21.jpg', '9781982137274'
    UNION ALL SELECT 22, 'Ikigai', 'Hector Garcia and Francesc Miralles', 'Penguin Life', 'A Japanese-inspired guide to finding purpose and a meaningful daily life.', 2017, 'real-22.jpg', '9780143130727'
    UNION ALL SELECT 37, 'Educated', 'Tara Westover', 'Random House', 'A memoir about education, family, and the struggle to build a new life.', 2018, 'real-37.jpg', '9780399590504'
    UNION ALL SELECT 38, 'The Power of Now', 'Eckhart Tolle', 'New World Library', 'A guide to mindfulness and living more fully in the present moment.', 1999, 'real-38.jpg', '9781577314806'
    UNION ALL SELECT 39, 'Rich Dad Poor Dad', 'Robert T. Kiyosaki', 'Plata Publishing', 'Personal finance lessons about money, work, and building financial independence.', 1997, 'real-39.jpg', '9781612680194'
) real_books ON real_books.id_buku = b.id_buku
SET b.judul_buku = real_books.judul_buku,
    b.penulis_buku = real_books.penulis_buku,
    b.penerbit_buku = real_books.penerbit_buku,
    b.deskripsi = real_books.deskripsi,
    b.tahun_terbit = real_books.tahun_terbit,
    b.cover_buku = real_books.cover_buku,
    b.pdf_buku = NULL;
