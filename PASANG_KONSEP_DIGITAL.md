# Pemasangan Konsep Perpustakaan Digital

1. Pastikan sedang berada di branch `second`.
2. Salin folder dalam paket ini ke root project dan pilih **Replace**.
3. Import `database/migration_digital_library.sql` melalui phpMyAdmin satu kali.
4. Pastikan setiap buku mempunyai `cover_buku` dan `pdf_buku`.
5. Buka halaman user, pilih buku, lalu klik **Mulai Membaca**.

Fitur yang ditambahkan:

- membaca satu PDF lengkap tanpa peminjaman atau pembayaran;
- PDF.js dengan tombol halaman sebelumnya/berikutnya;
- halaman terakhir tersimpan otomatis;
- riwayat dan persentase bacaan;
- tombol favorit;
- laporan aktivitas `membaca_buku`;
- statistik admin untuk pembaca aktif dan buku selesai dibaca.

Folder lama `user/peminjaman` dan `admin/peminjaman` boleh dibiarkan dahulu sebagai arsip karena sudah tidak ditautkan dari navigasi. Jangan drop tabel peminjaman sebelum membuat backup database.

Setelah pengujian berhasil:

```powershell
git add .
git commit -m "ubah konsep menjadi perpustakaan digital"
git push -u origin second
```
