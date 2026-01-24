<?php
session_start();
unset($_SESSION['swal_mhs']);
unset($_SESSION['swal_konfirmasi']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wisuda Unibi - Login Petugas</title>
  <link rel="stylesheet" href="../../style/login-style-petugas.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="login-page">

<?php if (isset($_SESSION['swal_petugas'])): ?>
<script>
Swal.fire({
  icon: "<?= $_SESSION['swal_petugas']['icon']; ?>",
  title: "<?= $_SESSION['swal_petugas']['title']; ?>",
  text: "<?= $_SESSION['swal_petugas']['text']; ?>"
});
</script>
<?php unset($_SESSION['swal_petugas']); endif; ?>

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
      <a href="/UNIBI_WISUDA/index.php" class="switch-btn">Mahasiswa</a>
      <button type="button" class="switch-btn active">Petugas</button>
    </div>

    <form action="/UNIBI_WISUDA/models/controllers/login_petugas.controller.php" method="POST">
      <div class="form-group">
        <label>Nama Petugas</label>
        <input type="text" name="nim" placeholder="Masukkan Nama Petugas" required>
      </div>

      <div class="form-group password-wrapper">
        <label>Password</label>
        <input type="password" id="password" name="password" placeholder="Masukkan Password" required>
        <i class="fa-solid toggle-icon" id="togglePassword"></i>
      </div>

      <button type="submit" name="login_petugas">Login</button>
    </form>
  </div>
</div>

<script>
  const passwordInput = document.getElementById('password');
  const toggleIcon = document.getElementById('togglePassword');

  toggleIcon.addEventListener('click', function () {
    const isHidden = passwordInput.type === 'password';
    passwordInput.type = isHidden ? 'text' : 'password';
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
  });
</script>

<!-- LOGIN PETUGAS SALAH - akun tidak ditemukan -->
<?php if (isset($_SESSION['swal_petugas'])): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'Login Gagal',
    text: '<?= $_SESSION['swal_petugas']; ?>'
  });
</script>
<?php unset($_SESSION['swal_petugas']); endif; ?>

</body>
</html>