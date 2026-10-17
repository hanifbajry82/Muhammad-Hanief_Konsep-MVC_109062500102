<?php /** @var int $code @var string $message */ ?>
<div class="error-page">
    <h1><?= (int) $code ?></h1>
    <p><?= e($message) ?></p>
    <a class="btn btn-primary" href="index.php?page=matakuliah">Kembali ke daftar</a>
</div>
