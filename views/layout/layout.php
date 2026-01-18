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
    
    // Jika di edit_wisuda.php, anggap kelola_mahasiswa.php yang aktif
    if ($currentPage === 'edit_wisuda.php') {
        return $page === 'kelola_mahasiswa.php' ? 'active' : '';
    }
    
    return $currentPage === $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Dashboard' ?></title>

    <link rel="stylesheet" href="/UNIBI_WISUDA/style/style.css">

<?php if ($_SESSION['role'] === 'petugas'): ?>
    <link rel="stylesheet" href="/UNIBI_WISUDA/style/petugas/dashboard.css">
    <link rel="stylesheet" href="/UNIBI_WISUDA/style/petugas/kelola_mahasiswa.css">
<?php endif; ?>

<?php if ($_SESSION['role'] === 'mahasiswa'): ?>
    <link rel="stylesheet" href="/UNIBI_WISUDA/style/mahasiswa/dashboard.css">
    <link rel="stylesheet" href="/UNIBI_WISUDA/style/mahasiswa/form_pendamping.css">
<?php endif; ?>

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
                <!-- <a href="/UNIBI_WISUDA/models/controllers/logout.controller.php" -->
                <a href="#"
                 onclick="logout()" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="sidebar">
      <div class="sidebar-header">
    <img src="/UNIBI_WISUDA/uploads/logo.png" class="logo">
    </div>

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

    <?php if ($_SESSION['role'] === 'mahasiswa'): ?>
    <a href="/UNIBI_WISUDA/views/mahasiswa/dashboard_mahasiswa.php"
       class="sidebar-link <?= isActive('dashboard_mahasiswa.php') ?>">
        <i class="fas fa-home"></i> Dashboard
    </a>

    <a href="/UNIBI_WISUDA/views/mahasiswa/form_pendamping.php"
       class="sidebar-link <?= isActive('form_pendamping.php') ?>">
        <i class="fas fa-user-friends"></i> Form Pendamping
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

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/UNIBI_WISUDA/script/index.js"></script>

<!-- NOTIF KONFIRMASI SUCCES -->
<?php if (isset($_SESSION['swal_success'])): ?>
<script>
Swal.fire({
  icon: 'success',
  title: 'Berhasil',
  text: '<?= $_SESSION['swal_success']; ?>',
  confirmButtonText: 'OK'
});
</script>
<?php unset($_SESSION['swal_success']); endif; ?>

<!-- NOTIF HAPUS WISUDAWAN -->
<script>
function hapusWisuda(id) {
  Swal.fire({
    title: 'Yakin ingin menghapus?',
    text: 'Data mahasiswa dan wisuda akan dihapus permanen!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href =
        '../../models/controllers/hapus_wisuda.controller.php?id_proses=' + id;
    }
  });
}
</script>

<!-- NOTIF KONFIRMASI WISUDAWAN -->
<script>
function konfirmasiWisuda(id) {
  Swal.fire({
    title: 'Konfirmasi Wisuda?',
    text: 'Status mahasiswa akan dikonfirmasi dan tidak bisa dibatalkan.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#28a745',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, konfirmasi',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href =
        '../../models/controllers/konfirmasi_proses.controller.php?id_proses=' + id;
    }
  });
}
</script>

<!-- NOTIF LOGIN MHS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['swal'])): ?>
<script>
Swal.fire({
  icon: '<?= $_SESSION['swal']['icon'] ?>',
  title: '<?= $_SESSION['swal']['title'] ?>',
  text: '<?= $_SESSION['swal']['text'] ?>'
});
</script>
<?php unset($_SESSION['swal']); endif; ?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php include_once __DIR__ . '/swal.php'; ?>
</body>

</body>
</html>

