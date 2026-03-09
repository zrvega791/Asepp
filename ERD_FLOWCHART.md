# Entity Relationship Diagram (ERD)

Berikut adalah gambaran relasi antar tabel dalam aplikasi:

```mermaid
erDiagram
    ROLES ||--o{ USERS : "has"
    USERS ||--o{ PEMINJAMAN : "makes"
    USERS ||--o{ LOG_AKTIVITAS : "does"
    KATEGORI ||--o{ ALAT : "classifies"
    ALAT ||--o{ PEMINJAMAN : "is borrowed"
    PEMINJAMAN ||--|| PENGEMBALIAN : "is settled"

    USERS {
        int id PK
        string username
        string password
        string nama_lengkap
        int role_id FK
    }

    ROLES {
        int id PK
        string role_name
    }

    ALAT {
        int id PK
        string nama_alat
        int kategori_id FK
        int stok
        text deskripsi
    }

    PEMINJAMAN {
        int id PK
        int user_id FK
        int alat_id FK
        date tgl_pinjam
        date tgl_kembali_rencana
        enum status
    }

    PENGEMBALIAN {
        int id PK
        int peminjaman_id FK
        date tgl_kembali_aktual
        decimal denda
    }
```

# Flowchart Sistem

## 1. Alur Peminjaman
1. User (Peminjam) memilih alat.
2. Sistem mengecek stok via Stored Procedure.
3. Jika stok > 0, data tersimpan dengan status `pending`.
4. Admin/Petugas melihat daftar pengajuan.
5. Admin/Petugas klik "Setujui".
6. Trigger berjalan: Stok alat berkurang 1.

## 2. Alur Pengembalian
1. Admin/Petugas klik "Kembalikan" pada data yang berstatus `approved`.
2. Sistem memanggil Function `hitung_denda`.
3. Data tersimpan di tabel `pengembalian`.
4. Trigger berjalan: Stok alat bertambah 1.
5. Status peminjaman berubah menjadi `returned`.
```
