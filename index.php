<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wisuda Unibi</title>
  <link rel="stylesheet" href="style/style.css">
</head>

<body class="login-page">

<div class="login-container">

  <div class="left-card">
    <img src="uploads/logo.png" />
    <h2>UNIBI</h2>
    <p>"Be The Young Entrepreneur"</p>
    <span>Happy Graduation</span>
  </div>

  <div class="right-card">
    <h3>MASUK</h3>

    <div class="login-switch">
      <button onclick="window.location.href='/UNIBI_WISUDA/views/mahasiswa/login_mahasiswa.php'">Mahasiswa</button>
      <button onclick="window.location.href='/UNIBI_WISUDA/views/petugas/login_petugas.php'">Petugas</button>
    </div>

    <form action="/UNIBI_WISUDA/models/controllers/login.controller.php" method="POST">
      <input type="text" name="nim" placeholder="Masukkan NIM" required>
      <input type="password" name="password" placeholder="Masukkan Password" required>
    </form>

    <p>Belum daftar? <a href="views/mahasiswa/register.php">Registrasi di sini</a></p>
  </div>

</div>

</body>


</html>