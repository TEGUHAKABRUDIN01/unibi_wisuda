<?php
include_once __DIR__ . '/../../config/config.php';

session_start();

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi Wisuda</title>
  <link rel="stylesheet" href="../../style/register-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="register-page">

<div class="register-container">

  <div class="register-left">
    <img src="../../uploads/logo.png" alt="Logo UNIBI" />
    <h2>UNIBI</h2>
    <p>"Be The Young Entrepreneur"</p>
    <span>Registrasi Wisuda</span>
  </div>

  <div class="register-right">
    <h3>DAFTAR WISUDA</h3>

    <form class="register-form" action="/UNIBI_WISUDA/models/controllers/register.controller.php" method="POST" enctype="multipart/form-data">

      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" placeholder="Masukkan Nama Lengkap">
      </div>

      <div class="form-group">
        <label>NIM</label>
        <input type="number" name="nim" placeholder="Masukkan Nomor Induk Mahasiswa" >
      </div>

      <div class="form-group">
        <label>Program Studi</label>
        <select name="id_prodi" >
          <option value="">-- Pilih Program Studi --</option>
          <?php
          $query = mysqli_query($conn, "SELECT id_prodi, nama_prodi FROM prodi ORDER BY nama_prodi ASC");
          while ($row = mysqli_fetch_assoc($query)) {
            echo "<option value='{$row['id_prodi']}'>{$row['nama_prodi']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan Password" >
      </div>

      <div class="form-group">
        <label>Upload SK Lulus (PDF)</label>
        <input type="file" name="sk_wisuda" accept=".pdf" >
      </div>

      <button type="submit" name="register_mahasiswa">Daftar</button>
    </form>

    <p>Sudah punya akun? <a href="/UNIBI_WISUDA/index.php">Login disini</a></p>
  </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
<?php if (isset($_SESSION['swal_error'])): ?>
  Swal.fire({
    icon: '<?= $_SESSION['swal_error']['icon']; ?>',
    title: '<?= $_SESSION['swal_error']['title']; ?>',
    text: '<?= $_SESSION['swal_error']['text']; ?>',
    confirmButtonColor: '#d33'
  });
<?php unset($_SESSION['swal_error']); endif; ?>

<?php if (isset($_SESSION['swal_konfirmasi'])): ?>
  Swal.fire({
    icon: '<?= $_SESSION['swal_konfirmasi']['icon']; ?>',
    title: '<?= $_SESSION['swal_konfirmasi']['title']; ?>',
    text: '<?= $_SESSION['swal_konfirmasi']['text']; ?>',
    confirmButtonColor: '#188E69'
  });
<?php unset($_SESSION['swal_konfirmasi']); endif; ?>
</script>

</body>

</html>


