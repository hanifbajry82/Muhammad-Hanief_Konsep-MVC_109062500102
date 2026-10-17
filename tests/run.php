<?php
declare(strict_types=1);

/**
 * Test runner tanpa dependensi (tidak perlu Composer/PHPUnit).
 *
 * Jalankan : php tests/run.php
 *  - Unit test (validator, CSRF, escaping) selalu berjalan.
 *  - Integration test Model berjalan jika database `akademik` dapat diakses.
 *    Semua perubahan data dibungkus transaksi lalu di-ROLLBACK sehingga data
 *    asli tidak berubah.
 * Exit code 0 = semua lulus, 1 = ada yang gagal.
 */

$root = dirname(__DIR__);
require $root . '/config/database.php';
require $root . '/app/helpers/functions.php';
require $root . '/app/helpers/Csrf.php';
require $root . '/app/helpers/MataKuliahValidator.php';
require $root . '/app/models/MataKuliah.php';

$passed = 0;
$failed = 0;

function check(string $name, bool $condition): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] $name\n";
    } else {
        $failed++;
        echo "  [FAIL] $name\n";
    }
}

$valid = [
    'kode_mk' => 'si999', 'nama_mk' => 'Rekayasa Perangkat Lunak', 'sks' => '3',
    'semester' => '4', 'jenis' => 'Wajib', 'dosen_pengampu' => 'Dr. Contoh',
];

echo "== Validator ==\n";
$r = MataKuliahValidator::validate($valid);
check('input valid tidak menghasilkan error', $r['errors'] === []);
check('kode diubah ke huruf besar', $r['data']['kode_mk'] === 'SI999');
check('sks & semester dikonversi ke int', $r['data']['sks'] === 3 && $r['data']['semester'] === 4);

$r = MataKuliahValidator::validate([]);
check('input kosong: 6 field error', count($r['errors']) === 6);

$r = MataKuliahValidator::validate(['sks' => '0'] + $valid);
check('sks 0 ditolak', isset($r['errors']['sks']));
$r = MataKuliahValidator::validate(['sks' => '7'] + $valid);
check('sks 7 ditolak', isset($r['errors']['sks']));
$r = MataKuliahValidator::validate(['sks' => 'abc'] + $valid);
check('sks non-angka ditolak', isset($r['errors']['sks']));
$r = MataKuliahValidator::validate(['sks' => '2.5'] + $valid);
check('sks desimal ditolak', isset($r['errors']['sks']));
$r = MataKuliahValidator::validate(['semester' => '9'] + $valid);
check('semester 9 ditolak', isset($r['errors']['semester']));
$r = MataKuliahValidator::validate(['jenis' => 'Lainnya'] + $valid);
check('jenis di luar whitelist ditolak', isset($r['errors']['jenis']));
$r = MataKuliahValidator::validate(['kode_mk' => 'A B!'] + $valid);
check('kode berisi karakter ilegal ditolak', isset($r['errors']['kode_mk']));
$r = MataKuliahValidator::validate(['nama_mk' => str_repeat('x', 101)] + $valid);
check('nama > 100 karakter ditolak', isset($r['errors']['nama_mk']));
$r = MataKuliahValidator::validate(['nama_mk' => '   '] + $valid);
check('nama berisi spasi saja ditolak', isset($r['errors']['nama_mk']));
$r = MataKuliahValidator::validate(['nama_mk' => ['x']] + $valid);
check('input array (nama_mk[]) ditolak tanpa warning', isset($r['errors']['nama_mk']));

echo "== CSRF & escaping ==\n";
$_SESSION = [];
$token = Csrf::token();
check('token 64 karakter hex', (bool) preg_match('/^[a-f0-9]{64}$/', $token));
check('token valid diterima', Csrf::verify($token));
check('token salah ditolak', !Csrf::verify('salah'));
check('token null ditolak', !Csrf::verify(null));
check('token array ditolak', !Csrf::verify([$token]));
check('e() meng-escape tag & kutip', e('<script>"x"</script>') === '&lt;script&gt;&quot;x&quot;&lt;/script&gt;');
check('e() meng-escape kutip tunggal', e("O'Reilly") === 'O&#039;Reilly');

echo "== Model (integration) ==\n";
try {
    $db = (new Database())->connect();
} catch (PDOException $e) {
    $db = null;
    echo "  [SKIP] database tidak dapat diakses: " . $e->getMessage() . "\n";
}

if ($db) {
    $db->beginTransaction();
    try {
        $model = new MataKuliah($db);
        $before = count($model->getAll());

        $id = $model->insert(['kode_mk' => 'TST001', 'nama_mk' => 'Mata Kuliah Uji', 'sks' => 2,
                              'semester' => 1, 'jenis' => 'Pilihan', 'dosen_pengampu' => 'Penguji']);
        check('insert mengembalikan id > 0', $id > 0);
        check('getAll bertambah 1', count($model->getAll()) === $before + 1);

        $row = $model->findById($id);
        check('findById menemukan data', is_array($row) && $row['kode_mk'] === 'TST001');
        check('findById id tak ada -> false', $model->findById(999999999) === false);

        check('kodeExists true untuk kode terpakai', $model->kodeExists('TST001'));
        check('kodeExists false jika dikecualikan id sendiri', !$model->kodeExists('TST001', $id));
        check('kodeExists false untuk kode baru', !$model->kodeExists('TST404'));

        $model->update($id, ['kode_mk' => 'TST001', 'nama_mk' => 'Nama Baru', 'sks' => 4,
                             'semester' => 2, 'jenis' => 'Wajib', 'dosen_pengampu' => 'Penguji 2']);
        $row = $model->findById($id);
        check('update mengubah data', $row['nama_mk'] === 'Nama Baru' && (int) $row['sks'] === 4);

        // Percobaan SQL injection harus diperlakukan sebagai data biasa.
        $evil = "x'); DROP TABLE mata_kuliah;--";
        $id2 = $model->insert(['kode_mk' => 'TST002', 'nama_mk' => $evil, 'sks' => 1,
                               'semester' => 1, 'jenis' => 'Wajib', 'dosen_pengampu' => 'Penguji']);
        check('SQL injection tersimpan sebagai teks biasa', $model->findById($id2)['nama_mk'] === $evil);
        check('tabel masih ada setelah percobaan injection', count($model->getAll()) === $before + 2);

        $dup = false;
        try {
            $model->insert(['kode_mk' => 'TST001', 'nama_mk' => 'Duplikat', 'sks' => 1,
                            'semester' => 1, 'jenis' => 'Wajib', 'dosen_pengampu' => 'X']);
        } catch (PDOException $e) {
            $dup = $e->getCode() === '23000';
        }
        check('constraint UNIQUE kode_mk menolak duplikat', $dup);

        check('delete data ada -> true', $model->delete($id) === true);
        check('delete data sudah hilang -> false', $model->delete($id) === false);
    } finally {
        $db->rollBack();
    }
}

echo "\nHasil: $passed lulus, $failed gagal\n";
exit($failed === 0 ? 0 : 1);
