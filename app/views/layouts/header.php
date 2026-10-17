<?php /** @var string $title */ $flash = Flash::pull(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Data Mata Kuliah') ?> - Akademik</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="container">
        <a class="brand" href="index.php?page=matakuliah">Akademik &middot; Data Mata Kuliah</a>
    </div>
</header>
<main class="container">
<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
<?php endif; ?>
