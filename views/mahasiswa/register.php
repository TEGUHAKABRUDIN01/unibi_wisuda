<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

$manajemen = getLatestManajemen($conn);
$manajemen_phase = $manajemen ? getManajemenPhase($manajemen) : 'not-configured';
$registration_open = $manajemen_phase === 'aktif';

// Redirect ke login jika manajemen tidak aktif
if (!$registration_open) {
    $_SESSION['registration_message'] = [
        'type' => 'warning',
        'title' => 'Pendaftaran Belum Dibuka',
        'text' => 'Pendaftaran hanya dapat dilakukan saat periode wisuda aktif.'
    ];
    header("Location: /UNIBI_WISUDA/views/mahasiswa/login_mahasiswa.php");
    exit;
}

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
          <label>Fakultas</label>
          <select id="id_fakultas" name="id_fakultas" class="<?= in_array('id_fakultas', $_SESSION['error_fields'] ?? []) ? 'error' : '' ?>" required onchange="loadNpmFormat()">
            <option value="">-- Pilih Fakultas Terlebih Dahulu --</option>
            <?php
            $query_fak = mysqli_query($conn, "SELECT id_fakultas, nama_fakultas FROM fakultas ORDER BY nama_fakultas ASC");
            while ($row_fak = mysqli_fetch_assoc($query_fak)) {
              $selected = ($_SESSION['form_data']['id_fakultas'] ?? '') == $row_fak['id_fakultas'] ? 'selected' : '';
              echo "<option value='{$row_fak['id_fakultas']}' $selected>{$row_fak['nama_fakultas']}</option>";
            }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>NIM / NPM</label>
          <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span id="npm_format_display" style="background: #f0f0f0; padding: 0.75rem; border-radius: 4px; min-width: 80px; text-align: center; font-weight: bold;">-</span>
            <input type="text" id="nim" name="nim" placeholder="Lanjutkan NPM sesuai format" 
                   value="<?= htmlspecialchars($_SESSION['form_data']['nim'] ?? '') ?>"
                   class="<?= in_array('nim', $_SESSION['error_fields'] ?? []) ? 'error' : '' ?>" style="flex: 1;">
          </div>
          <small style="display: block; margin-top: 0.25rem; color: #666;">Format NPM akan muncul otomatis setelah memilih fakultas</small>
        </div>

        <div class="form-group">
          <label>Program Studi</label>
          <select name="id_prodi" class="<?= in_array('id_prodi', $_SESSION['error_fields'] ?? []) ? 'error' : '' ?>" required onchange="filterProdiByFakultas()">
            <option value="">-- Pilih Program Studi --</option>
            <?php
            $query_prodi = mysqli_query($conn, "SELECT p.id_prodi, p.nama_prodi, p.id_fakultas FROM prodi p ORDER BY p.nama_prodi ASC");
            $all_prodi = [];
            while ($row_prodi = mysqli_fetch_assoc($query_prodi)) {
              $all_prodi[] = $row_prodi;
              $selected = ($_SESSION['form_data']['id_prodi'] ?? '') == $row_prodi['id_prodi'] ? 'selected' : '';
              echo "<option value='{$row_prodi['id_prodi']}' data-fakultas='{$row_prodi['id_fakultas']}' $selected>{$row_prodi['nama_prodi']}</option>";
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

  function loadNpmFormat() {
    const fakultasSelect = document.getElementById('id_fakultas');
    const nimInput = document.getElementById('nim');
    const formatDisplay = document.getElementById('npm_format_display');
    
    if (!fakultasSelect.value) {
      formatDisplay.textContent = '-';
      nimInput.placeholder = 'Pilih fakultas dulu';
      return;
    }

    fetch('/UNIBI_WISUDA/views/mahasiswa/get_npm_format.php?id_fakultas=' + fakultasSelect.value)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success' && data.npm_format) {
          formatDisplay.textContent = data.npm_format;
          nimInput.placeholder = 'Lanjutkan NPM sesuai format';
          nimInput.dataset.format = data.npm_format;
        }
      });

    filterProdiByFakultas();
  }

  function filterProdiByFakultas() {
    const fakultasSelect = document.getElementById('id_fakultas');
    const prodiSelect = document.querySelector('select[name="id_prodi"]');
    
    Array.from(prodiSelect.options).forEach(option => {
      if (!option.value) return;
      if (option.dataset.fakultas === fakultasSelect.value) {
        option.style.display = '';
      } else {
        option.style.display = 'none';
      }
    });
  }

  // Jalankan format NPM & filter prodi saat load jika fakultas sudah terpilih
  document.addEventListener('DOMContentLoaded', function() {
    const fakultasSelect = document.getElementById('id_fakultas');
    if (fakultasSelect && fakultasSelect.value) {
      loadNpmFormat();
    }
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