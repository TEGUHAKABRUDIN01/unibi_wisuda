<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wisuda Unibi</title>
</head>

<body>

  <div class="login-container">
    <h2>Login Petugas</h2>

    <!-- Tombol menu login -->
    <div>
      <a href="/UNIBI_WISUDA/index.php">
        <button type="button">Mahasiswa</button>
      </a>
      <button type="button">Petugas</button>
    </div>
    <br>

    <!-- Form login khusus petugas -->
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

</body>
</html>