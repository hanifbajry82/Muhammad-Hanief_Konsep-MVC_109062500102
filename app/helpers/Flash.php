<?php
declare(strict_types=1);

/**
 * Flash message: pesan sekali tampil yang bertahan melewati satu redirect
 * (pola Post/Redirect/Get). Dipakai untuk indikator sukses/gagal operasi.
 */
class Flash
{
    public const SUCCESS = 'success';
    public const ERROR   = 'error';

    public static function set(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /** Mengambil pesan lalu menghapusnya dari session. */
    public static function pull(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
