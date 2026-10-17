<?php
/**
 * Partial form dipakai bersama oleh create.php dan edit.php.
 *
 * @var string $action URL tujuan form
 * @var array  $values Nilai field (untuk prefill / old input)
 * @var array  $errors Pesan error per field
 * @var string $submitLabel
 * @var int|null $id   Hanya ada pada mode edit
 */
$field = static fn(string $name): string => e($values[$name] ?? '');
$error = static fn(string $name): string => isset($errors[$name])
    ? '<small class="error">' . e($errors[$name]) . '</small>' : '';
?>
<?php if ($errors): ?>
    <div class="alert alert-error" role="alert">Data belum valid. Periksa kembali isian di bawah.</div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" novalidate>
    <?= Csrf::field() ?>
    <?php if (isset($id)): ?><input type="hidden" name="id" value="<?= (int) $id ?>"><?php endif; ?>

    <div class="field">
        <label for="kode_mk">Kode Mata Kuliah</label>
        <input id="kode_mk" type="text" name="kode_mk" maxlength="10" value="<?= $field('kode_mk') ?>" required>
        <?= $error('kode_mk') ?>
    </div>
    <div class="field">
        <label for="nama_mk">Nama Mata Kuliah</label>
        <input id="nama_mk" type="text" name="nama_mk" maxlength="100" value="<?= $field('nama_mk') ?>" required>
        <?= $error('nama_mk') ?>
    </div>
    <div class="row">
        <div class="field">
            <label for="sks">SKS (1-6)</label>
            <input id="sks" type="number" name="sks" min="1" max="6" value="<?= $field('sks') ?>" required>
            <?= $error('sks') ?>
        </div>
        <div class="field">
            <label for="semester">Semester (1-8)</label>
            <input id="semester" type="number" name="semester" min="1" max="8" value="<?= $field('semester') ?>" required>
            <?= $error('semester') ?>
        </div>
        <div class="field">
            <label for="jenis">Jenis</label>
            <select id="jenis" name="jenis">
                <?php foreach (MataKuliahValidator::JENIS as $opt): ?>
                    <option value="<?= e($opt) ?>" <?= ($values['jenis'] ?? '') === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                <?php endforeach; ?>
            </select>
            <?= $error('jenis') ?>
        </div>
    </div>
    <div class="field">
        <label for="dosen_pengampu">Dosen Pengampu</label>
        <input id="dosen_pengampu" type="text" name="dosen_pengampu" maxlength="100" value="<?= $field('dosen_pengampu') ?>" required>
        <?= $error('dosen_pengampu') ?>
    </div>

    <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    <a class="btn" href="index.php?page=matakuliah">Kembali</a>
</form>
