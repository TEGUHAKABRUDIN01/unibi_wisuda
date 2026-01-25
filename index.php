<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wisuda Unibi</title>
  <link rel="stylesheet" href="style/login-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .password-wrapper {
      position: relative;
    }
    .password-wrapper input {
      width: 100%;
      padding-right: 40px; /* beri ruang untuk ikon */
    }
    .toggle-icon {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%); /* sejajarkan vertikal */
      font-size: 1.1em;
      cursor: pointer;
      color: #666;
    }
  </style>
</head>

<body class="login-page">

<div class="login-container">

  <div class="left-card">
    <img src="uploads/logo.png" alt="Logo UNIBI" />
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

      <div class="password-wrapper">
        <input type="password" id="password" name="password" placeholder="Masukkan Password" required>
        <i class="fa-solid fa-eye toggle-icon" id="togglePassword"></i>
      </div>

      <button type="submit" name="login_mahasiswa">Login</button>
    </form>

    <p>Belum daftar? <a href="views/mahasiswa/register.php">Daftar disini</a></p>
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

</body>
</html>