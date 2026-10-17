# MVC PHP Native - Data Mata Kuliah

Aplikasi CRUD **Data Mata Kuliah** untuk mempelajari pola Model-View-Controller pada PHP Native,
memakai PDO + prepared statement, dan dikembangkan dengan riwayat Git bertahap.

## Fitur
- Menampilkan daftar mata kuliah (urut semester)
- Menambah, mengubah, dan menghapus mata kuliah
- Validasi input (server-side) dengan pesan error per field
- Indikator **berhasil/gagal** setiap operasi (flash message)
- Keamanan dasar: prepared statement, escaping output (XSS), token CSRF, aksi ubah data hanya via POST

## Entitas `mata_kuliah`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT UNSIGNED, PK, AUTO_INCREMENT | |
| kode_mk | VARCHAR(10), UNIQUE | contoh `SI305` |
| nama_mk | VARCHAR(100) | |
| sks | TINYINT UNSIGNED | 1-6 |
| semester | TINYINT UNSIGNED | 1-8 |
| jenis | ENUM('Wajib','Pilihan') | |
| dosen_pengampu | VARCHAR(100) | |
| created_at / updated_at | TIMESTAMP | otomatis |

## Kebutuhan
- PHP 8.0+ dengan ekstensi `pdo_mysql` dan `mbstring` (aktif secara default di XAMPP/Laragon)
- MySQL 8 / MariaDB 10.4+
- Web server lokal (XAMPP / Laragon) atau `php -S`
- Git

## Instalasi
1. Salin/clone project ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Impor database: `mysql -u root -p < database/akademik.sql` (atau phpMyAdmin > Import).
3. Jika user/password MySQL bukan `root`/kosong: salin `config/database.local.example.php`
   menjadi `config/database.local.php`, lalu ubah isinya (file ini tidak ikut di-commit).
4. Buka `http://localhost/mvc-matakuliah/public/index.php`.

Alternatif tanpa Apache: `php -S localhost:8000 -t public`, lalu buka `http://localhost:8000/index.php`.

## Struktur Folder
```
mvc-matakuliah/
├── app/
│   ├── controllers/MataKuliahController.php   # alur request
│   ├── models/MataKuliah.php                  # akses data (PDO)
│   ├── helpers/                               # validator, CSRF, flash, e()
│   └── views/ (layouts, matakuliah, errors)   # tampilan HTML
├── config/database.php                        # koneksi PDO
├── database/akademik.sql                      # skema + data awal
├── public/ (index.php, assets/style.css)      # front controller / router
├── tests/run.php                              # automated test
├── .gitignore
└── README.md
```

## Pengujian
`php tests/run.php` (database `akademik` harus sudah diimpor; data asli aman karena
test memakai transaksi lalu rollback).

## Histori Git
Lihat tahapan pengembangan dengan:
`git log --oneline --graph --decorate`

5969ba5 (HEAD -> main, origin/main) docs: add installation and MVC documentation
cf2b2ed test: add automated validator and model tests
1c94d3d feat: add views and stylesheet
0a86480 feat: add controller and front controller routing
b24e18d feat: add validator, CSRF, flash and escaping helpers
e7c1f2d feat: add MataKuliah model with prepared statements
b435357 feat: add PDO database connection
0478932 feat: add mata_kuliah schema and seed data
d7d437b chore: initialize project with gitignore
