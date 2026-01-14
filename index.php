<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wisuda Unibi</title>
</head>

<body>

  <div class="login-container">
    <h2>Login Wisudawan</h2>

    <!-- Tombol menu login -->
    <div>
      <!-- Mahasiswa: tetap di halaman ini -->
      <button type="button" onclick="window.location.href='/UNIBI_WISUDA/index.php'">Mahasiswa</button>
      <!-- Petugas: pindah ke halaman login petugas -->
      <button type="button" onclick="window.location.href='/UNIBI_WISUDA/views/petugas/login_petugas.php'">Petugas</button>
    </div>
    <br>

    <!-- Form login mahasiswa -->
    <form action="/UNIBI_WISUDA/models/controllers/login.controller.php" method="POST">
      <div class="form-group">
        <label>NIM</label>
        <input type="text" name="nim" placeholder="Masukkan NIM" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan Password" required>
      </div>

      <button type="submit" name="login_mahasiswa">Login</button>
    </form>

    <p>Belum daftar? <a href="views/mahasiswa/register.php">Registrasi di sini</a></p>
  </div>

</body>
</html>