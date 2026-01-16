<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi global
if (!isset($_SESSION['role'])) {
    header("Location: /UNIBI_WISUDA/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="/UNIBI_WISUDA/style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dashboard-page">

<div class="sidebar">
    <img src="/UNIBI_WISUDA/uploads/logo.png" class="logo">

    <?php if ($_SESSION['role'] === 'petugas'): ?>
        <a href="/UNIBI_WISUDA/views/petugas/dashboard_petugas.php">Dashboard</a>
        <a href="/UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php">Kelola Mahasiswa</a>
        <a href="/UNIBI_WISUDA/views/petugas/detail_wisuda.php">Detail Wisuda</a>
    <?php endif; ?>

    <a href="/UNIBI_WISUDA/models/controllers/logout.controller.php">Logout</a>
</div>

<div class="main">
    <?= $content ?>
</div>

</body>
</html>
