<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Registrasi Wisuda</title>
</head>

<body>
  <div class="container">
    <h2>Form Pendaftaran Wisudawan</h2>

    <form action="/UNIBI_WISUDA/models/controllers/register.controllers.php" method="POST" enctype="multipart/form-data">

      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" placeholder="Nama Lengkap" required>
      </div>

      <div class="form-group">
        <label>NIM</label>
        <input type="text" name="nim" placeholder="Nomor Induk Mahasiswa" required>
      </div>

      <div class="form-group">
        <label>Program Studi</label>
        <select name="id_prodi" required>
          <option value="">-- Pilih Program Studi --</option>
          <option value="1">Informatika</option>
          <option value="2">Sistem Informasi</option>
        </select>
      </div>

      <div class="form-group">
        <label>Buat Password</label>
        <input type="password" name="password" placeholder="Password untuk login" required>
      </div>

      <div class="form-group">
        <label>Upload SK Lulus (PDF/JPG)</label>
        <input type="file" name="sk_wisuda" accept=".pdf,.jpg,.jpeg,.png" required>
      </div>

      <button type="submit" name="register_mahasiswa">Daftar Antrian</button>
    </form>
  </div>
</body>

</html>