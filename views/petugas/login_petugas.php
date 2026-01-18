<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id"> <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wisuda Unibi - Login Petugas</title>
  <link rel="stylesheet" href="../../style/login-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="login-page">

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

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Masukkan Password" required>
        </div>

        <button type="submit" name="login_petugas">Login</button>
      </form>
    </div>

  </div>
</html>


<!-- LOGIN PETUGAS SALAH - akun tidak ditemukan -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['swal_error'])): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'Login Gagal',
    text: '<?= $_SESSION['swal_error']; ?>'
  });
</script>
<?php unset($_SESSION['swal_error']); endif; ?>
