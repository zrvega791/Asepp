# Dokumentasi Teknis - Aplikasi Peminjaman Alat

Dokumentasi ini menjelaskan arsitektur, basis data, dan alur kerja aplikasi untuk keperluan penilaian UKK RPL.

## 1. Arsitektur Folder
Aplikasi dibangun dengan struktur modular berbasis PHP Native:
- `/config`: Pengaturan koneksi database (PDO).
- `/public`: Aset publik seperti CSS, Gambar, dan Javascript.
- `/views`: Template tampilan (UI) yang dipisahkan berdasarkan modul.
- `index.php`: Jalur masuk utama (Front Controller) & Router.

## 2. Struktur Database (ERD)
Aplikasi ini menggunakan 7 tabel utama dalam database `ukk_peminjaman_alat`:

- **roles**: Menyimpan level user (admin, petugas, peminjam).
- **users**: Data akun pengguna dengan password terenkripsi MD5.
- **kategori**: Kategori alat (elektronik, olahraga, dll).
- **alat**: Data inventaris alat beserta stoknya.
- **peminjaman**: Mencatat pengajuan pinjam oleh user.
- **pengembalian**: Mencatat data saat alat dikembalikan (termasuk denda).
- **log_aktivitas**: Audit trail aktivitas user dalam sistem.

### Relasi Utama:
- `users` (N) -> (1) `roles`
- `alat` (N) -> (1) `kategori`
- `peminjaman` (N) -> (1) `users`
- `peminjaman` (N) -> (1) `alat`
- `pengembalian` (1) -> (1) `peminjaman`

## 3. Fitur Keamanan
- **MD5 Hashing**: Password tidak disimpan dalam teks biasa.
- **Role-Based Access Control (RBAC)**: Pembatasan menu berdasarkan level login di `index.php` dan setiap tampilan.
- **Sanitasi Data**: Menggunakan PDO Prepared Statements untuk mencegah SQL Injection.
- **Session Management**: Verifikasi login di setiap halaman untuk mencegah akses langsung via URL.

## 4. Logika Bisnis Khusus
### a. Trigger `tr_peminjaman_approved`
Otomatis memperbarui stok di tabel `alat` saat status peminjaman berubah:
- `pending` -> `approved`: `stok = stok - 1`
- `approved` -> `returned`: `stok = stok + 1`

### b. Function `hitung_denda`
Menghitung denda secara dinamis:
- Jika `tgl_kembali_aktual` > `tgl_kembali_rencana`, denda = selisih hari * Rp 5.000.

### c. Stored Procedure `ajukan_peminjaman`
Memastikan user tidak bisa meminjam alat jika stok 0 (Zero Stock Validation) sebelum data masuk ke tabel peminjaman.

## 5. Panduan Pengujian
1. **Scenario 1 (Login)**: Masuk sebagai `admin` dengan password `admin123`. Pastikan menu "Manajemen User" muncul.
2. **Scenario 2 (Stok Alat)**: Coba pinjam alat dengan stok 0. Sistem harus menolak via SP.
3. **Scenario 3 (Denda)**: Ubah tanggal rencana kembali menjadi kemarin di database, lalu lakukan pengembalian. Pastikan denda terhitung.
