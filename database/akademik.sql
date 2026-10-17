-- =====================================================================
-- File    : database/akademik.sql
-- Fungsi  : membuat database `akademik`, tabel `mata_kuliah`, dan data awal.
-- Impor   : mysql -u root -p < database/akademik.sql   (atau phpMyAdmin > Import)
-- Catatan : skrip ini aman dijalankan ulang (IF NOT EXISTS + INSERT IGNORE).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS akademik
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE akademik;

CREATE TABLE IF NOT EXISTS mata_kuliah (
    id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    kode_mk         VARCHAR(10)      NOT NULL,
    nama_mk         VARCHAR(100)     NOT NULL,
    sks             TINYINT UNSIGNED NOT NULL,
    semester        TINYINT UNSIGNED NOT NULL,
    jenis           ENUM('Wajib','Pilihan') NOT NULL DEFAULT 'Wajib',
    dosen_pengampu  VARCHAR(100)     NOT NULL,
    created_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_mata_kuliah_kode (kode_mk),
    CONSTRAINT chk_mata_kuliah_sks      CHECK (sks BETWEEN 1 AND 6),
    CONSTRAINT chk_mata_kuliah_semester CHECK (semester BETWEEN 1 AND 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis, dosen_pengampu) VALUES
('SI101', 'Pengantar Sistem Informasi', 3, 1, 'Wajib',   'Dr. Rina Kusuma'),
('SI203', 'Basis Data',                 3, 3, 'Wajib',   'Budi Hartono, M.Kom.'),
('SI305', 'Pemrograman Web',            3, 5, 'Wajib',   'Sari Wulandari, M.T.'),
('SI412', 'Keamanan Informasi',         2, 7, 'Pilihan', 'Andi Nugroho, M.Cs.');
