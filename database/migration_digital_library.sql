CREATE TABLE IF NOT EXISTS reading_progress (
 id_progress INT AUTO_INCREMENT PRIMARY KEY, id_user INT NOT NULL, id_buku INT NOT NULL,
 halaman_terakhir INT NOT NULL DEFAULT 1, total_halaman INT NOT NULL DEFAULT 0,
 status ENUM('membaca','selesai') NOT NULL DEFAULT 'membaca', terakhir_dibaca DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uk_progress_user_book (id_user,id_buku),
 FOREIGN KEY (id_user) REFERENCES tbl_user(id_user) ON DELETE CASCADE,
 FOREIGN KEY (id_buku) REFERENCES tbl_buku(id_buku) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS tbl_favorit (
 id_user INT NOT NULL, id_buku INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id_user,id_buku),
 FOREIGN KEY (id_user) REFERENCES tbl_user(id_user) ON DELETE CASCADE,
 FOREIGN KEY (id_buku) REFERENCES tbl_buku(id_buku) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
