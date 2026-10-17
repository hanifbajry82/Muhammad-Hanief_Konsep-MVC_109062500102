<?php
declare(strict_types=1);

/**
 * Model MataKuliah
 *
 * Satu-satunya komponen yang berbicara langsung dengan tabel `mata_kuliah`.
 * Seluruh query memakai prepared statement. Model tidak mencetak HTML dan
 * tidak membaca $_GET/$_POST.
 */
class MataKuliah
{
    private const COLUMNS = 'id, kode_mk, nama_mk, sks, semester, jenis, dosen_pengampu';

    public function __construct(private PDO $db) {}

    /** @return array[] Seluruh mata kuliah, urut semester lalu kode. */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT ' . self::COLUMNS . ' FROM mata_kuliah ORDER BY semester, kode_mk'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT ' . self::COLUMNS . ' FROM mata_kuliah WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Apakah kode sudah dipakai? $exceptId dipakai saat edit agar data
     * tidak dianggap bentrok dengan dirinya sendiri.
     */
    public function kodeExists(string $kode, ?int $exceptId = null): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM mata_kuliah WHERE kode_mk = :kode AND id <> :id'
        );
        $stmt->execute([':kode' => $kode, ':id' => $exceptId ?? 0]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** @return int ID baris yang baru dibuat. */
    public function insert(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, jenis, dosen_pengampu)
             VALUES (:kode_mk, :nama_mk, :sks, :semester, :jenis, :dosen_pengampu)'
        );
        $stmt->execute($this->params($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE mata_kuliah
                SET kode_mk = :kode_mk, nama_mk = :nama_mk, sks = :sks,
                    semester = :semester, jenis = :jenis,
                    dosen_pengampu = :dosen_pengampu
              WHERE id = :id'
        );
        return $stmt->execute($this->params($data) + [':id' => $id]);
    }

    /** @return bool true jika ada baris yang benar-benar terhapus. */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM mata_kuliah WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /** Memetakan data tervalidasi ke placeholder (whitelist kolom). */
    private function params(array $data): array
    {
        return [
            ':kode_mk'        => $data['kode_mk'],
            ':nama_mk'        => $data['nama_mk'],
            ':sks'            => $data['sks'],
            ':semester'       => $data['semester'],
            ':jenis'          => $data['jenis'],
            ':dosen_pengampu' => $data['dosen_pengampu'],
        ];
    }
}
