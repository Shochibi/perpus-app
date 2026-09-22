# 📖 Panduan Implementasi Sistem Premium Pembayaran

## Ringkasan Perubahan
Sistem peminjaman buku (rental) telah diubah menjadi **sistem pembayaran premium**. Pengguna gratis dapat melihat **preview 10 halaman pertama** saja, sedangkan pengguna **premium** dapat mengakses semua buku tanpa batasan.

---

## 🔧 Langkah Instalasi

### 1. Jalankan Database Migration
Buka phpMyAdmin atau terminal MySQL dan jalankan file:
```bash
database/migration_premium.sql
```

Atau jalankan query berikut secara manual:
```sql
ALTER TABLE tbl_user ADD COLUMN is_premium TINYINT DEFAULT 0;
ALTER TABLE tbl_user ADD COLUMN tanggal_premium_hingga DATE DEFAULT NULL;
ALTER TABLE tbl_user ADD COLUMN metode_pembayaran VARCHAR(50) DEFAULT NULL;

CREATE TABLE tbl_pembayaran (
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

CREATE INDEX idx_user_premium ON tbl_user(is_premium);
CREATE INDEX idx_pembayaran_user ON tbl_pembayaran(id_user);
CREATE INDEX idx_pembayaran_status ON tbl_pembayaran(status);
```

### 2. Verifikasi File-File Baru
Pastikan file-file berikut sudah ada dan ter-create:
- ✅ `/user/pembayaran/beli_premium.php` - Halaman beli premium
- ✅ `/user/pembayaran/index.php` - Halaman status premium user
- ✅ `/admin/pembayaran/index.php` - Admin panel pembayaran

### 3. Restart Web Server
Restart XAMPP/server Anda agar perubahan diterapkan.

### 4. Konfigurasi E-Wallet (⭐ PENTING)
Edit file `config/app.php` dan ubah nomor e-wallet admin sesuai kebutuhan:

```php
// Buka: config/app.php
// Ubah baris ini dengan nomor e-wallet Anda:
define("ADMIN_EWALLET", "+62-812-3456-7890");  // ← Ganti dengan nomor Anda
```

**Konfigurasi Lain yang Bisa Diubah:**
```php
define("PREMIUM_PRICE", 14999);           // Harga premium (Rp)
define("PREMIUM_DURATION", 30);           // Durasi premium (hari)
define("PAYMENT_METHOD", "e-wallet");     // Metode pembayaran
define("MAX_PREVIEW_PAGES", 10);          // Batas halaman preview user gratis
```

---

## 📋 Fitur Utama

### A. Halaman User

#### 1. **Katalog Buku** (`/user/buku/index.php`)
- Tetap sama, user gratis bisa lihat daftar buku

#### 2. **Detail Buku** (`/user/buku/detail.php`)
**Perubahan:**
- Jika user PREMIUM: Tombol "Baca Buku" → akses penuh
- Jika user GRATIS: 
  - Tombol "Lihat Preview (10 halaman)" 
  - Tombol "Upgrade ke Premium"

#### 3. **Baca Buku** (`/user/buku/read.php`)
**Perubahan:**
- Pengguna PREMIUM: Dapat membaca semua halaman PDF
- Pengguna GRATIS: 
  - Hanya bisa lihat halaman 1-10 (preview)
  - Tampil notifikasi: "Preview Mode - 10 halaman pertama"
  - Tombol "Upgrade ke Premium" di bawah PDF viewer

#### 4. **Beli Premium** (`/user/pembayaran/beli_premium.php`) - **BARU**
- Form pembelian membership premium
- Harga: **Rp 14.999** per 30 hari
- Fitur yang didapat:
  - ✓ Baca semua buku tanpa batasan
  - ✓ Akses 1000+ buku
  - ✓ Download PDF
  - ✓ Dukungan prioritas
- Tombol "Bayar Sekarang"

#### 5. **Status Premium** (`/user/pembayaran/index.php`) - **BARU**
- Tampilkan status premium user (aktif/expired/tidak ada)
- Jika premium: tunjukkan tanggal berakhir dan sisa hari
- Riwayat pembayaran (tabel transaksi)
- Tombol perpanjang premium

### B. Menu Admin

#### **Admin Pembayaran** (`/admin/pembayaran/index.php`) - **BARU**
Fitur:
- 📊 Statistik:
  - Total transaksi
  - Jumlah pending, berhasil, ditolak
  - Total revenue (Rp)
- 🔍 Filter pembayaran: Semua, Pending, Berhasil, Ditolak
- 📋 Tabel pembayaran dengan kolom:
  - User (nama & username)
  - Nominal Rp
  - Durasi 30 hari
  - Tanggal pembayaran
  - Metode pembayaran
  - Status pembayaran
  - **Aksi:**
    - Untuk PENDING: Tombol "Setujui" dan "Tolak"
    - Untuk BERHASIL/GAGAL: Tampil "Selesai"

**Proses Persetujuan:**
1. Admin klik "Setujui" untuk pembayaran pending
2. Sistem otomatis:
   - Update status pembayaran menjadi "berhasil"
   - Set `tanggal_pembayaran = NOW()`
   - Update `is_premium = 1` untuk user
   - Hitung `tanggal_premium_hingga` (hari ini + 30 hari)
   - Log aktivitas

---

## 💻 Struktur Database Baru

### Tabel: `tbl_user` (Perubahan)
```
Kolom Baru:
- is_premium (TINYINT, default 0) - 1=premium, 0=gratis
- tanggal_premium_hingga (DATE) - Kapan premium berakhir
- metode_pembayaran (VARCHAR) - Cara pembayaran terakhir
```

### Tabel Baru: `tbl_pembayaran`
```
id_pembayaran (INT, PK, auto-increment)
id_user (INT, FK) - Referensi tbl_user
nominal (INT) - Jumlah pembayaran
metode_pembayaran (VARCHAR) - Tipe pembayaran
status (ENUM) - pending/berhasil/gagal
tanggal_pembayaran (DATETIME) - Kapan dibayar/disetujui
tanggal_dibuat (TIMESTAMP) - Waktu record dibuat
durasi_hari (INT) - Durasi premium (30 hari)
```

---

## 🔄 User Journey

### Flow Pengguna Gratis → Premium (BARU ⭐)

1. **User buka halaman detail buku**
   - Melihat tombol "Lihat Preview (10 halaman)"
   - Melihat tombol "Upgrade ke Premium"

2. **User klik "Lihat Preview"**
   - Masuk ke halaman baca (read.php)
   - Lihat notifikasi "Preview Mode - 10 halaman pertama"
   - Hanya halaman 1-10 yang bisa dilihat
   - Ada tombol "Upgrade ke Premium"

3. **User klik "Upgrade ke Premium"**
   - Masuk ke `/user/pembayaran/beli_premium.php`
   - Lihat detail paket: **Rp 14.999 / 30 hari**
   - Lihat instruksi pembayaran E-Wallet dengan nomor admin
   - Klik "Bayar Sekarang"
   - **Record pembayaran dibuat dengan status "PENDING"** ⚠️

4. **User lakukan transfer E-Wallet**
   - User buka e-wallet mereka (GCash, PayMaya, OVO, Dana, dll)
   - Transfer ke nomor e-wallet admin
   - Cantumkan catatan dengan nama mereka
   - User bisa cek status di `/user/pembayaran/index.php` → "Menunggu Persetujuan"

5. **Admin menerima pembayaran**
   - Admin lihat notifikasi pembayaran masuk
   - Admin buka `/admin/pembayaran/index.php` → lihat pembayaran PENDING
   - Admin cocokkan nama user dengan catatan transfer
   - Admin klik tombol "Setujui"

6. **Sistem approve otomatis**
   - Status pembayaran → "berhasil"
   - User status → `is_premium = 1`
   - `tanggal_premium_hingga` = hari ini + 30 hari
   - Log activity tercatat

7. **User sudah premium** ✅
   - Bisa baca **semua halaman** PDF tanpa preview limitation
   - Lihat status "Premium Aktif" di `/user/pembayaran/index.php`
   - Lihat tanggal premium berakhir
   - Premium berlaku 30 hari dari tanggal approval

---

## 📱 Metode Pembayaran E-Wallet

**E-Wallet yang Diterima:**
- GCash (Philippines)
- PayMaya (Philippines)
- OVO (Indonesia)
- Dana (Indonesia)
- Linkaja (Indonesia)

**Alamat Pembayaran:**
```
Nomor E-Wallet Admin: +62-812-3456-7890 (ganti di config/app.php)
```

**Instruksi User:**
1. Buka aplikasi e-wallet
2. Pilih "Kirim Uang" / "Transfer"
3. Masukkan nomor admin: `+62-812-3456-7890`
4. Nominal: `Rp 14.999`
5. Catatan: `Premium [Nama User]`
6. Kirim

---

## 🔑 Perubahan Kode Utama

### 1. **Proses Pembayaran (user/pembayaran/beli_premium.php)**
```php
// SEBELUM: Langsung berhasil & user premium aktif
// SESUDAH: Status PENDING, tunggu admin approve
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $metode = PAYMENT_METHOD;      // Ambil dari config (e-wallet)
    $status = "pending";           // Status PENDING, bukan berhasil
    
    // Insert ke tbl_pembayaran tanpa update user
    mysqli_prepare()->execute();   // TIDAK update is_premium
    
    // User lihat "Menunggu Persetujuan Admin"
}
```

### 2. **Admin Approve (admin/pembayaran/index.php)**
```php
// Admin klik "Setujui"
if (isset($_GET["approve"])) {
    // 1. Update tbl_pembayaran status = 'berhasil'
    // 2. Hitung tanggal_premium_hingga
    // 3. Update tbl_user set is_premium=1, tanggal_premium_hingga=...
    // 4. Log activity
}
```

### 3. **User Status Page (user/pembayaran/index.php)**
```php
// Tampilkan:
// - Jika ada pembayaran PENDING → "⏳ Menunggu Persetujuan Pembayaran"
// - Instruksi transfer e-wallet
// - Jika BERHASIL → "✓ Premium Aktif" + tanggal expiry
```

### 4. **Konfigurasi (config/app.php)**
```php
define("ADMIN_EWALLET", "+62-812-3456-7890");  // Nomor e-wallet admin
define("PREMIUM_PRICE", 14999);                 // Harga (Rp)
define("PREMIUM_DURATION", 30);                 // Durasi (hari)
define("PAYMENT_METHOD", "e-wallet");           // Metode
define("MAX_PREVIEW_PAGES", 10);                // Preview halaman
```

---

## 🎯 Testing Checklist

- [ ] Database migration berhasil (3 kolom baru + 1 tabel baru)
- [ ] Config ADMIN_EWALLET sudah diubah ke nomor E-Wallet Anda
- [ ] User gratis bisa lihat daftar buku
- [ ] User gratis klik "Lihat Preview" → muncul preview warning
- [ ] User gratis lihat "Upgrade ke Premium" di detail buku
- [ ] User klik "Upgrade ke Premium" → masuk form pembayaran
- [ ] Form pembayaran tampil dengan metode E-Wallet & nomor admin
- [ ] User klik "Bayar Sekarang" → record terbuat di tbl_pembayaran **STATUS PENDING**
- [ ] User lihat status "⏳ Menunggu Persetujuan Pembayaran" di `/user/pembayaran/index.php`
- [ ] Admin buka `/admin/pembayaran/index.php` → lihat pembayaran pending
- [ ] Admin klik "Setujui" → pembayaran status jadi "berhasil"
- [ ] User otomatis jadi premium (is_premium=1) setelah admin approve
- [ ] User lihat "✓ Premium Aktif" di dashboard premium
- [ ] Riwayat pembayaran terlihat dengan status "Berhasil"
- [ ] Admin klik "Setujui" → pembayaran status jadi "berhasil"
- [ ] User otomatis jadi premium (is_premium=1)
- [ ] User premium buka buku → bisa baca semua halaman (preview warning hilang)
- [ ] User premium lihat `/user/pembayaran/index.php` → status "Premium Aktif"
- [ ] Riwayat pembayaran terlihat di user dashboard

---

## 📝 Catatan Penting ⭐

1. **Konfigurasi E-Wallet** (WAJIB DIUBAH):
   - Edit `config/app.php`
   - Ubah `ADMIN_EWALLET` ke nomor e-wallet Anda sebenarnya
   - Default: `+62-812-3456-7890` (hanya contoh)

2. **Harga & Durasi Premium**:
   - Rp 14.999 per 30 hari (bisa diubah di `config/app.php`)
   - Edit: `PREMIUM_PRICE` dan `PREMIUM_DURATION`

3. **Status Pembayaran**:
   - **PENDING** = User bayar, tunggu admin approve
   - **BERHASIL** = Admin approve, user jadi premium
   - **GAGAL** = Pembayaran ditolak, user bisa coba lagi

4. **Flow Pembayaran** (PENTING):
   - User klik "Bayar Sekarang" → record `tbl_pembayaran` dengan status PENDING
   - User BELUM jadi premium
   - User harus transfer e-wallet ke nomor admin
   - Admin approve di `/admin/pembayaran/index.php` → baru user jadi premium

5. **Preview Limit**: Hardcoded ke 10 halaman. Ubah di:
   - `config/app.php` → `MAX_PREVIEW_PAGES`
   - atau edit langsung di `/user/buku/read.php`

6. **Tabel Lama** (Peminjaman):
   - `tbl_peminjaman` dan `tbl_detail_peminjaman` masih ada
   - Bisa dihapus atau di-backup jika tidak digunakan lagi
   - Menu admin sudah berubah dari Peminjaman ke Pembayaran

7. **Email Notifikasi**: Belum implementasi. Bisa ditambah untuk:
   - Konfirmasi pembayaran ke user
   - Notifikasi approval ke admin
   - Reminder premium akan expired

---

## 🛠️ Troubleshooting

### Error: "Kolom is_premium tidak ditemukan"
**Solusi:** Jalankan migration SQL di phpMyAdmin terlebih dahulu:
```
database/migration_premium.sql
```

### User masih lihat "pending" padahal sudah 30 menit
**Solusi:**
- Refresh halaman user
- Admin lupa approve pembayaran di `/admin/pembayaran/index.php`
- Cek apakah pembayaran sudah masuk ke e-wallet admin

### E-Wallet admin nomor tidak sesuai
**Solusi:**
- Edit `config/app.php`
- Ubah `ADMIN_EWALLET` ke nomor Anda:
```php
define("ADMIN_EWALLET", "nomor-anda-di-sini");
```
- Semua halaman pembayaran akan otomatis update

### User gratis bisa baca semua halaman PDF
**Solusi:** 
- Cek di `read.php`, pastikan kondisi `if (!$isPremium)` bekerja
- Verifikasi di database: `SELECT is_premium FROM tbl_user WHERE id_user=X`

### Admin tidak bisa approve pembayaran
**Solusi:** 
- Cek middleware admin (`middleware/admin.php`)
- Pastikan user logged in sebagai admin
- Cek browser console (F12) untuk error message

### Pembayaran user statusnya "gagal" tapi mereka sudah transfer
**Solusi:**
- Admin bisa ubah status pembayaran di database atau reject & biarkan user bayar lagi
- Atau admin klik "Setujui" untuk mengapprove

### Premium user masih lihat preview mode
**Solusi:**
- Refresh halaman
- Cek database: `SELECT is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=X`
- Pastikan `is_premium=1` dan `tanggal_premium_hingga > hari ini`

### Form pembayaran tidak tampil dengan benar
**Solusi:**
- Clear browser cache (Ctrl+Shift+Delete)
- Cek `config/app.php` sudah ter-define semua konstanta
- Cek error di browser console (F12 → Console tab)
- Pastikan user logged in sebagai admin

### Premium user masih lihat preview mode
**Solusi:**
- Cek database: `SELECT is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=X`
- Pastikan `tanggal_premium_hingga` > hari hari ini

---

## 📞 Support

Jika ada error atau pertanyaan, cek:
1. Error log di browser (F12 → Console)
2. PHP error log di `/xampp/apache/logs/`
3. Database di phpmyadmin

Selamat! Sistem premium perpustakaan digital siap digunakan! 🎉
