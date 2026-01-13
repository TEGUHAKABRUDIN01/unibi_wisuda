<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LOGIN</title>
</head>

<body>

  <div class="login-container">
    <h2>Login Wisudawan</h2>
    <form action="models/controllers/login.controllers.php" method="POST">
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
    <p>Belum daftar? <a href="views/mahasiswa/mahasiswa.php">Registrasi di sini</a></p>
  </div>

</body>

</html>