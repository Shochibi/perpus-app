-- Migration: Tambah Sistem Premium ke Database
-- Mengubah dari sistem peminjaman (rental) ke sistem pembayaran (payment/premium)

-- 1. Tambah kolom premium ke tbl_user
ALTER TABLE tbl_user ADD COLUMN is_premium TINYINT DEFAULT 0;
ALTER TABLE tbl_user ADD COLUMN tanggal_premium_hingga DATE DEFAULT NULL;
ALTER TABLE tbl_user ADD COLUMN metode_pembayaran VARCHAR(50) DEFAULT NULL;

-- 2. Buat tabel transaksi pembayaran
CREATE TABLE IF NOT EXISTS tbl_pembayaran (
  id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  nominal INT NOT NULL,
  metode_pembayaran VARCHAR(50) NOT NULL,
  status ENUM('pending', 'berhasil', 'gagal') DEFAULT 'pending',
  tanggal_pembayaran DATETIME,
  tanggal_dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  durasi_hari INT DEFAULT 30,
  FOREIGN KEY (id_user) REFERENCES tbl_user(id_user) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Rename tbl_peminjaman dan tbl_detail_peminjaman untuk backup (opsional, atau bisa dihapus)
-- Jika ingin keep old data, gunakan:
-- ALTER TABLE tbl_detail_peminjaman RENAME TO tbl_detail_peminjaman_backup;
-- ALTER TABLE tbl_peminjaman RENAME TO tbl_peminjaman_backup;

-- 4. Buat index untuk performa
CREATE INDEX idx_user_premium ON tbl_user(is_premium);
CREATE INDEX idx_pembayaran_user ON tbl_pembayaran(id_user);
CREATE INDEX idx_pembayaran_status ON tbl_pembayaran(status);
