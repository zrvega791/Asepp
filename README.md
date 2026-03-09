# Aplikasi Peminjaman Alat - UKK RPL 2025/2026

Aplikasi berbasis web untuk manajemen peminjaman alat sekolah/perusahaan dengan fitur lengkap sesuai standar UKK RPL.

## 🚀 Fitur Utama
- **Multi-Role Access**: Admin, Petugas, dan Peminjam.
- **Modern Dashboard**: Statistik real-time dan log aktivitas.
- **Manajemen Inventaris**: CRUD Alat & Kategori (Admin).
- **Workflow Peminjaman**: Pengajuan -> Persetujuan -> Pengembalian.
- **Sistem Denda**: Perhitungan otomatis menggunakan Function MySQL.
- **Database Trigger**: Update stok otomatis saat peminjaman disetujui atau dikembalikan.
- **Security**: Password MD5 dan Prepared Statements (PDO).

## 🛠️ Teknologi
- **Backend**: Native PHP (MVC Pattern)
- **Frontend**: Vanilla CSS (Modern Design System), FontAwesome, Google Fonts.
- **Database**: MySQL (MariaDB).

## 📋 Cara Instalasi
1. Pastikan **XAMPP** sudah terinstal dan Apache & MySQL dalam posisi **Running**.
2. Buat database baru bernama `ukk_peminjaman_alat` di phpMyAdmin.
3. Import file `database.sql` ke database tersebut.
4. Letakkan folder project ini di `C:/xampp/htdocs/`.
5. Akses melalui browser: `http://localhost/UKK-Asepp27/`

## 🔐 Akun Default
| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Petugas | petugas | petugas123 |
| Peminjam | siswa | siswa123 |

## 🧪 Hasil Pengujian (Test Cases)
1. **Login**: Berhasil masuk sesuai role dengan password MD5.
2. **Ajukan Pinjam**: Stok alat tidak berkurang sebelum disetujui (Trigger logic).
3. **Persetujuan**: Stok alat otomatis berkurang saat status diubah menjadi 'approved'.
4. **Pengembalian**: Sistem menghitung denda otomatis jika melewati batas waktu.
5. **Akses Kontrol**: Peminjam tidak dapat mengakses menu Manajemen User.
