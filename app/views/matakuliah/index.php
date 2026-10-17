<?php /** @var array[] $items */ ?>
<div class="page-head">
    <h1>Daftar Mata Kuliah</h1>
    <a class="btn btn-primary" href="index.php?page=matakuliah&amp;action=create">+ Tambah Data</a>
</div>

<?php if (!$items): ?>
    <p class="empty">Belum ada data mata kuliah.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>Kode</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Smt</th>
            <th>Jenis</th><th>Dosen Pengampu</th><th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $m): ?>
        <tr>
            <td><?= e($m['kode_mk']) ?></td>
            <td><?= e($m['nama_mk']) ?></td>
            <td><?= e($m['sks']) ?></td>
            <td><?= e($m['semester']) ?></td>
            <td><span class="badge badge-<?= $m['jenis'] === 'Wajib' ? 'wajib' : 'pilihan' ?>"><?= e($m['jenis']) ?></span></td>
            <td><?= e($m['dosen_pengampu']) ?></td>
            <td class="actions">
                <a class="btn btn-small" href="index.php?page=matakuliah&amp;action=edit&amp;id=<?= (int) $m['id'] ?>">Edit</a>
                <form method="post" action="index.php?page=matakuliah&amp;action=delete"
                      onsubmit="return confirm('Hapus mata kuliah ini?')">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                    <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
