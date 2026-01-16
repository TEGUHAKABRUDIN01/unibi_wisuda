<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi global
if (!isset($_SESSION['role'])) {
    header("Location: /UNIBI_WISUDA/index.php");
    exit;
}

// Fungsi untuk deteksi halaman aktif
function isActive($page) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $page ? 'active' : '';
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

<nav class="navbar">
    <div class="navbar-right">
        <div class="user-dropdown">
            <button class="user-btn" onclick="toggleUserDropdown()">
                <i class="fas fa-user-circle"></i>
            </button>
            <div class="dropdown-menu" id="userDropdown">
                <div class="dropdown-header">
                    <span><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></span>
                </div>
                <a href="/UNIBI_WISUDA/models/controllers/logout.controller.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="sidebar">
    <img src="/UNIBI_WISUDA/uploads/logo.png" class="logo">

    <?php if ($_SESSION['role'] === 'petugas'): ?>
        <a href="/UNIBI_WISUDA/views/petugas/dashboard_petugas.php" class="sidebar-link <?= isActive('dashboard_petugas.php') ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="/UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php" class="sidebar-link <?= isActive('kelola_mahasiswa.php') ?>">
            <i class="fas fa-users"></i> Kelola Mahasiswa
        </a>
        <a href="/UNIBI_WISUDA/views/petugas/detail_wisuda.php" class="sidebar-link <?= isActive('detail_wisuda.php') ?>">
            <i class="fas fa-graduation-cap"></i> Detail Wisuda
        </a>
    <?php endif; ?>
</div>

<div class="main">
    <?= $content ?>
</div>

<script>
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userBtn = document.querySelector('.user-btn');
        const dropdown = document.getElementById('userDropdown');
        if (!event.target.closest('.user-dropdown')) {
            dropdown.classList.remove('show');
        }
    });
</script>

</body>
</html>
