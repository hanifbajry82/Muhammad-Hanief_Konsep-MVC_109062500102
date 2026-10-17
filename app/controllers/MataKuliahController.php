<?php
declare(strict_types=1);

/**
 * Controller MataKuliah
 *
 * Menerima request dari router, memvalidasi input, memanggil Model, lalu
 * memilih View atau melakukan redirect. Tidak ada SQL dan tidak ada HTML
 * panjang di sini.
 */
class MataKuliahController
{
    private const LIST_URL = 'index.php?page=matakuliah';

    public function __construct(private MataKuliah $model) {}

    // ---------------------------------------------------------------- READ

    public function index(): void
    {
        $this->render('matakuliah/index', [
            'title' => 'Daftar Mata Kuliah',
            'items' => $this->model->getAll(),
        ]);
    }

    // -------------------------------------------------------------- CREATE

    public function create(): void
    {
        $this->render('matakuliah/create', [
            'title'  => 'Tambah Mata Kuliah',
            'values' => ['jenis' => 'Wajib'],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $this->guardPost();

        ['data' => $data, 'errors' => $errors] = MataKuliahValidator::validate($_POST);

        if (!isset($errors['kode_mk']) && $this->model->kodeExists($data['kode_mk'])) {
            $errors['kode_mk'] = 'Kode mata kuliah sudah digunakan.';
        }

        if ($errors) {
            http_response_code(422);
            $this->render('matakuliah/create', [
                'title' => 'Tambah Mata Kuliah', 'values' => $data, 'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model->insert($data);
        } catch (PDOException $e) {
            $this->failWithFlash($e, 'Data gagal disimpan karena kesalahan database.');
        }

        Flash::set(Flash::SUCCESS, 'Mata kuliah "' . $data['nama_mk'] . '" berhasil ditambahkan.');
        $this->redirect(self::LIST_URL);
    }

    // ---------------------------------------------------------------- EDIT

    public function edit(): void
    {
        $id   = $this->intParam($_GET['id'] ?? null);
        $item = $id > 0 ? $this->model->findById($id) : false;

        if (!$item) {
            $this->error(404, 'Data mata kuliah tidak ditemukan.');
            return;
        }

        $this->render('matakuliah/edit', [
            'title' => 'Edit Mata Kuliah', 'id' => $id, 'values' => $item, 'errors' => [],
        ]);
    }

    public function update(): void
    {
        $this->guardPost();

        $id = $this->intParam($_POST['id'] ?? null);
        if ($id <= 0 || !$this->model->findById($id)) {
            $this->error(404, 'Data mata kuliah tidak ditemukan.');
            return;
        }

        ['data' => $data, 'errors' => $errors] = MataKuliahValidator::validate($_POST);

        if (!isset($errors['kode_mk']) && $this->model->kodeExists($data['kode_mk'], $id)) {
            $errors['kode_mk'] = 'Kode mata kuliah sudah digunakan.';
        }

        if ($errors) {
            http_response_code(422);
            $this->render('matakuliah/edit', [
                'title' => 'Edit Mata Kuliah', 'id' => $id, 'values' => $data, 'errors' => $errors,
            ]);
            return;
        }

        try {
            $this->model->update($id, $data);
        } catch (PDOException $e) {
            $this->failWithFlash($e, 'Data gagal diperbarui karena kesalahan database.');
        }

        Flash::set(Flash::SUCCESS, 'Mata kuliah "' . $data['nama_mk'] . '" berhasil diperbarui.');
        $this->redirect(self::LIST_URL);
    }

    // -------------------------------------------------------------- DELETE

    public function delete(): void
    {
        $this->guardPost();

        $id   = $this->intParam($_POST['id'] ?? null);
        $item = $id > 0 ? $this->model->findById($id) : false;

        if (!$item) {
            Flash::set(Flash::ERROR, 'Data tidak ditemukan atau sudah dihapus.');
            $this->redirect(self::LIST_URL);
        }

        try {
            $this->model->delete($id);
        } catch (PDOException $e) {
            $this->failWithFlash($e, 'Data gagal dihapus karena kesalahan database.');
        }

        Flash::set(Flash::SUCCESS, 'Mata kuliah "' . $item['nama_mk'] . '" berhasil dihapus.');
        $this->redirect(self::LIST_URL);
    }

    // ------------------------------------------------------------ ERRORS

    /** Dipanggil juga oleh router untuk 404/405. */
    public function error(int $code, string $message): void
    {
        http_response_code($code);
        $this->render('errors/error', [
            'title' => 'Terjadi Kesalahan', 'code' => $code, 'message' => $message,
        ]);
    }

    // ----------------------------------------------------------- INTERNAL

    /** Aksi pengubah data hanya boleh lewat POST + token CSRF valid. */
    private function guardPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Allow: POST');
            $this->error(405, 'Metode request tidak diizinkan.');
            exit;
        }
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->error(403, 'Token keamanan tidak valid atau kedaluwarsa. Muat ulang halaman lalu coba lagi.');
            exit;
        }
    }

    /** Log detail teknis ke error_log; pengguna hanya melihat pesan umum. */
    private function failWithFlash(PDOException $e, string $userMessage): never
    {
        error_log('[MataKuliah] ' . $e->getMessage());
        Flash::set(Flash::ERROR, $userMessage);
        $this->redirect(self::LIST_URL);
    }

    private function intParam(mixed $value): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        return $int === false ? 0 : $int;
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    /** Memuat layout + view; $data diekspos sebagai variabel lokal. */
    private function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewDir = __DIR__ . '/../views/';
        require $viewDir . 'layouts/header.php';
        require $viewDir . $view . '.php';
        require $viewDir . 'layouts/footer.php';
    }
}
