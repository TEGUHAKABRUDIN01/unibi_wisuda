<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

$manajemen = getLatestManajemen($conn);
$manajemen_phase = getManajemenPhase($manajemen);
$registration_open = $manajemen_phase === 'running';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wisuda Unibi</title>

  <!-- ✅ LOAD SWEETALERT DULU -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="../../style/login-style.css">
</head>

<body class="login-page">

  <!-- ✅ NOTIF MAHASISWA GAGAL LOGIN -->
  <?php if (isset($_SESSION['swal_mhs'])): ?>
  <script>
    Swal.fire({
      icon: "<?= $_SESSION['swal_mhs']['icon'] ?>",
      title: "<?= $_SESSION['swal_mhs']['title'] ?>",
      text: "<?= $_SESSION['swal_mhs']['text'] ?>",
      confirmButtonText: "OK"
    });
  </script>
  <?php unset($_SESSION['swal_mhs']); endif; ?>

  <!-- ✅ NOTIF REGISTRASI BERHASIL -->
  <?php if (isset($_SESSION['swal_konfirmasi'])): ?>
  <script>
    Swal.fire({
      icon: "<?= $_SESSION['swal_konfirmasi']['icon'] ?>",
      title: "<?= $_SESSION['swal_konfirmasi']['title'] ?>",
      text: "<?= $_SESSION['swal_konfirmasi']['text'] ?>",
      confirmButtonText: "OK"
    });
  </script>
  <?php unset($_SESSION['swal_konfirmasi']); endif; ?>

  <div class="login-container">
    <div class="left-card">
      <img src="../../uploads/logo.png" alt="Logo UNIBI" />
      <h2>UNIBI</h2>
      <p>"Be The Young Entrepreneur"</p>
      <span>Happy Graduation</span>
    </div>

    <div class="right-card">
      <h3>MASUK</h3>

      <div class="login-switch">
        <button type="button" class="switch-btn active">Mahasiswa</button>
        <a href="/UNIBI_WISUDA/views/petugas/login_petugas.php" class="switch-btn">Petugas</a>
      </div>

      <form action="/UNIBI_WISUDA/models/controllers/login.controller.php" method="POST">
        <input type="text" name="nim" placeholder="Masukkan NIM" required>
        <input type="password" name="password" placeholder="Masukkan Password" required>
        <button type="submit">Login</button>
      </form>

      <?php if ($registration_open): ?>
        <p>Belum daftar? <a href="/UNIBI_WISUDA/views/mahasiswa/register.php">Daftar Sekarang</a></p>
      <?php else: ?>
        <?php if ($manajemen && $manajemen_phase === 'before-start'): ?>
            <p>Pendaftaran dimulai pada <?= htmlspecialchars($manajemen['tgl_mulai']) ?>.</p>
        <?php elseif ($manajemen && $manajemen_phase === 'ended'): ?>
            <p>Acara wisuda telah selesai. Pendaftaran ditutup.</p>
        <?php else: ?>
            <p>Pendaftaran belum dibuka. Hubungi admin untuk informasi.</p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>
