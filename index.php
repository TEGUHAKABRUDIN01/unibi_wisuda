<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wisuda Unibi</title>
  <link rel="stylesheet" href="style/login-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
      <input type="password" name="password" placeholder="Masukkan Password" required>
      <button type="submit" name="login_mahasiswa">Login</button>
    </form>

    <p>Belum daftar? <a href="views/mahasiswa/register.php">Daftar disini</a></p>
  </div>

</div>

</body>
</html>