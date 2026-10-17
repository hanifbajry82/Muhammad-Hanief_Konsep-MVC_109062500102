<?php
declare(strict_types=1);

/**
 * Proteksi CSRF berbasis token session.
 * Semua form POST wajib menyertakan Csrf::field().
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Input hidden siap pakai untuk diletakkan di dalam <form>. */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(self::token()) . '">';
    }

    /** Perbandingan waktu-konstan agar tahan timing attack. */
    public static function verify(mixed $submitted): bool
    {
        $expected = $_SESSION['csrf_token'] ?? '';
        return is_string($submitted)
            && $expected !== ''
            && hash_equals($expected, $submitted);
    }
}
