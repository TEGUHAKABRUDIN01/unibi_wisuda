<?php
include_once __DIR__ . '/../../config/config.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Registrasi Wisuda</title>
  <link rel="stylesheet" href="../../style/style.css">
</head>

<body class="register-page">

<div class="login-container">

  <!-- LEFT -->
  <div class="left-card">
    <h2>UNIBI</h2>
    <p>"Be The Young Entrepreneur"</p>
    <span>Registrasi Wisuda</span>
  </div>

  <!-- RIGHT -->
  <div class="right-card">
    <h3>DAFTAR WISUDA</h3>

    <form class="form-grid" action="/UNIBI_WISUDA/models/controllers/register.controller.php"
          method="POST"
          enctype="multipart/form-data">

      <div class="form-group full">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" placeholder="Nama Lengkap" required>
      </div>

      <div class="form-group full">
        <label>NIM</label>
        <input type="text" name="nim" placeholder="Nomor Induk Mahasiswa" required>
      </div>

      <div class="form-group full">
        <label>Program Studi</label>
        <select name="id_prodi" required>
          <option value="">-- Pilih Program Studi --</option>
          <?php
          $query = mysqli_query($conn, "SELECT id_prodi, nama_prodi FROM prodi ORDER BY nama_prodi ASC");
          while ($row = mysqli_fetch_assoc($query)) {
            echo "<option value='{$row['id_prodi']}'>{$row['nama_prodi']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group full">
        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>
      </div>

      <div class="form-group full">
        <label>Upload SK Lulus (PDF)</label>
        <input type="file" name="sk_wisuda" accept=".pdf" required>
      </div>

      <button type="submit" name="register_mahasiswa">Daftar</button>
    </form>

    <p style="margin-top:15px;text-align:center;">
      Sudah punya akun?
      <a href="/UNIBI_WISUDA/index.php">Login</a>
    </p>
  </div>

</div>

</body>


</html>