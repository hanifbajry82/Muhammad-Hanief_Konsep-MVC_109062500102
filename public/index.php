<?php
declare(strict_types=1);

/**
 * Front controller + router sederhana.
 * Semua request masuk lewat file ini.
 */

$root = dirname(__DIR__);

require $root . '/config/database.php';
require $root . '/app/helpers/functions.php';
require $root . '/app/helpers/Flash.php';
require $root . '/app/helpers/Csrf.php';
require $root . '/app/helpers/MataKuliahValidator.php';
require $root . '/app/models/MataKuliah.php';
require $root . '/app/controllers/MataKuliahController.php';

// Session untuk flash message & token CSRF.
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

try {
    $db         = (new Database())->connect();
    $controller = new MataKuliahController(new MataKuliah($db));

    $page   = $_GET['page']   ?? 'matakuliah';
    $action = $_GET['action'] ?? 'index';

    if ($page !== 'matakuliah') {
        $controller->error(404, 'Halaman tidak ditemukan.');
        exit;
    }

    match ($action) {
        'index'  => $controller->index(),
        'create' => $controller->create(),
        'store'  => $controller->store(),
        'edit'   => $controller->edit(),
        'update' => $controller->update(),
        'delete' => $controller->delete(),
        default  => $controller->error(404, 'Aksi tidak ditemukan.'),
    };
} catch (Throwable $e) {
    // Detail teknis hanya ke log server, bukan ke layar pengguna.
    error_log('[App] ' . $e::class . ': ' . $e->getMessage());
    http_response_code(500);
    exit('Terjadi kesalahan pada server. Pastikan database sudah diimpor dan config/database.php benar.');
}
