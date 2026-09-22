-- Migration: Sistem bookmark buku untuk user
CREATE TABLE IF NOT EXISTS tbl_bookmark (
  id_bookmark INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  id_buku INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_bookmark_user_book (id_user, id_buku),
  KEY idx_bookmark_user (id_user),
  CONSTRAINT fk_bookmark_user FOREIGN KEY (id_user) REFERENCES tbl_user(id_user) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_bookmark_book FOREIGN KEY (id_buku) REFERENCES tbl_buku(id_buku) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
