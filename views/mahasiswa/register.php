<?php
include_once __DIR__ . '/../../config/config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Registrasi Wisuda</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../style/register-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <input type="text" name="nama" placeholder="Masukkan Nama Lengkap"
               value="<?= htmlspecialchars($_SESSION['form_data']['nama'] ?? '') ?>"
               class="<?= in_array('nama', $_SESSION['error_fields'] ?? []) ? 'error' : '' ?>">
      </div>

      <div class="form-group">
        <label>NIM</label>
        <input type="text" name="nim" placeholder="Masukkan Nomor Induk Mahasiswa"
               value="<?= htmlspecialchars($_SESSION['form_data']['nim'] ?? '') ?>"
               class="<?= in_array('nim', $_SESSION['error_fields'] ?? []) ? 'error' : '' ?>">
      </div>

      <div class="form-group">
        <label>Program Studi</label>
        <select name="id_prodi" class="<?= in_array('id_prodi', $_SESSION['error_fields'] ?? []) ? 'error' : '' ?>">
          <option value="">-- Pilih Program Studi --</option>
          <?php
          $query = mysqli_query($conn, "SELECT id_prodi, nama_prodi FROM prodi ORDER BY nama_prodi ASC");
          while ($row = mysqli_fetch_assoc($query)) {
            $selected = ($_SESSION['form_data']['id_prodi'] ?? '') == $row['id_prodi'] ? 'selected' : '';
            echo "<option value='{$row['id_prodi']}' $selected>{$row['nama_prodi']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group password-wrapper">
        <label>Password</label>
        <input type="password" id="password" name="password" placeholder="Masukkan Password"
               value="<?= htmlspecialchars($_SESSION['form_data']['password'] ?? '') ?>"
               class="<?= in_array('password', $_SESSION['error_fields'] ?? []) ? 'error' : '' ?>">
        <i class="fa-solid fa-eye toggle-icon" id="togglePassword"></i>
      </div>

      <div class="form-group">
        <label>Upload SK Lulus (PDF)</label>
        <input type="file" name="sk_wisuda" accept=".pdf"
               class="<?= in_array('sk_wisuda', $_SESSION['error_fields'] ?? []) ? 'error' : '' ?>">
      </div>

      <button type="submit" name="register_mahasiswa">Daftar</button>
    </form>

    <p>Sudah punya akun? <a href="/UNIBI_WISUDA/index.php">Login disini</a></p>
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

<?php
unset($_SESSION['form_data']);
unset($_SESSION['error_fields']);
?>

</body>
</html>