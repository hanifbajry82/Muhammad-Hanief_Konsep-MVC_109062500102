<?php
declare(strict_types=1);

/**
 * Class Database
 *
 * Membuat koneksi PDO ke MySQL/MariaDB.
 *
 * Konfigurasi dibaca dari config/database.local.php (TIDAK di-commit, lihat
 * .gitignore) sehingga password asli tidak pernah masuk ke riwayat Git.
 * Jika file lokal tidak ada, nilai default XAMPP/Laragon dipakai.
 */
class Database
{
    private const DEFAULTS = [
        'host'    => 'localhost',
        'port'    => 3306,
        'name'    => 'akademik',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ];

    private array $config;

    /** @param array|null $config Override manual (dipakai oleh tests). */
    public function __construct(?array $config = null)
    {
        $this->config = array_merge(self::DEFAULTS, $config ?? $this->loadLocalConfig());
    }

    /** Membuat koneksi baru. Melempar PDOException jika gagal. */
    public function connect(): PDO
    {
        $c   = $this->config;
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $c['host'], (int) $c['port'], $c['name'], $c['charset']
        );

        return new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Prepared statement asli di sisi server (bukan emulasi).
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    private function loadLocalConfig(): array
    {
        $file = __DIR__ . '/database.local.php';
        if (!is_file($file)) {
            return [];
        }
        $override = require $file;
        return is_array($override) ? $override : [];
    }
}
