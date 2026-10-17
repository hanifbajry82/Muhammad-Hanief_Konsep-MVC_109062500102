<?php
declare(strict_types=1);

/**
 * Validasi & normalisasi input form Mata Kuliah.
 *
 * Dipisahkan dari Controller agar (1) Controller tetap ramping dan
 * (2) aturan validasi dapat diuji tanpa web server maupun database.
 */
final class MataKuliahValidator
{
    public const JENIS = ['Wajib', 'Pilihan'];

    /**
     * @param array $input Biasanya $_POST.
     * @return array{data: array, errors: array<string,string>}
     *         `data` selalu terisi (sudah di-trim) agar bisa ditampilkan
     *         kembali pada form; `errors` kosong jika semua valid.
     */
    public static function validate(array $input): array
    {
        $data = [
            'kode_mk'        => strtoupper(self::text($input['kode_mk'] ?? '')),
            'nama_mk'        => self::text($input['nama_mk'] ?? ''),
            'sks'            => self::text($input['sks'] ?? ''),
            'semester'       => self::text($input['semester'] ?? ''),
            'jenis'          => self::text($input['jenis'] ?? ''),
            'dosen_pengampu' => self::text($input['dosen_pengampu'] ?? ''),
        ];
        $errors = [];

        // Kode MK: huruf besar, angka, strip; 3-10 karakter.
        if ($data['kode_mk'] === '') {
            $errors['kode_mk'] = 'Kode mata kuliah wajib diisi.';
        } elseif (!preg_match('/^[A-Z0-9-]{3,10}$/', $data['kode_mk'])) {
            $errors['kode_mk'] = 'Kode 3-10 karakter: huruf, angka, atau tanda strip.';
        }

        // Nama MK
        if ($data['nama_mk'] === '') {
            $errors['nama_mk'] = 'Nama mata kuliah wajib diisi.';
        } elseif (mb_strlen($data['nama_mk']) < 3 || mb_strlen($data['nama_mk']) > 100) {
            $errors['nama_mk'] = 'Nama mata kuliah harus 3-100 karakter.';
        }

        // SKS & semester: bilangan bulat dalam rentang.
        $sks = filter_var($data['sks'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 6]]);
        if ($sks === false) {
            $errors['sks'] = 'SKS harus bilangan bulat 1-6.';
        } else {
            $data['sks'] = $sks;
        }

        $smt = filter_var($data['semester'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 8]]);
        if ($smt === false) {
            $errors['semester'] = 'Semester harus bilangan bulat 1-8.';
        } else {
            $data['semester'] = $smt;
        }

        // Jenis: whitelist.
        if (!in_array($data['jenis'], self::JENIS, true)) {
            $errors['jenis'] = 'Jenis harus Wajib atau Pilihan.';
        }

        // Dosen
        if ($data['dosen_pengampu'] === '') {
            $errors['dosen_pengampu'] = 'Dosen pengampu wajib diisi.';
        } elseif (mb_strlen($data['dosen_pengampu']) < 3 || mb_strlen($data['dosen_pengampu']) > 100) {
            $errors['dosen_pengampu'] = 'Nama dosen harus 3-100 karakter.';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    /** Hanya menerima skalar (menolak array seperti nama[]=x) lalu trim. */
    private static function text(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
