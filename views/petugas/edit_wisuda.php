<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

$manajemen = getLatestManajemen($conn);

if (!$manajemen || $manajemen['status'] !== 'aktif') {
    header("Location: /UNIBI_WISUDA/views/petugas/manajemen_wisuda.php");
    exit;
}

$id_proses = $_GET['id'] ?? null;
if (!$id_proses) {
  header("Location: dashboard_petugas.php");
  exit;
}

/* ===============================
   Ambil data lama
================================ */
$query = mysqli_query($conn, "
  SELECT 
    p.id_proses,
    p.id_mahasiswa,
    p.is_edited,
    m.nama_mahasiswa,
    m.nim,
    m.id_prodi,
    f.npm_format,
    m.jenis_peserta
  FROM proses_wisuda p
  JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
  JOIN fakultas f ON m.id_fakultas = f.id_fakultas
  WHERE p.id_proses = '$id_proses'
");

$data = mysqli_fetch_assoc($query);


if (!$data) {
  echo "<script>alert('Data tidak ditemukan'); window.location='dashboard_petugas.php';</script>";
  exit;
}

if ($data['jenis_peserta'] === 'susulan') {

    $prefix = '';
    $suffix_nim = $data['nim'];

} else {

    $prefix = $data['npm_format'];

    $suffix_nim = strpos($data['nim'], $prefix) === 0
        ? substr($data['nim'], strlen($prefix))
        : $data['nim'];
}

/* ===============================
   Buffer ke layout
================================ */
$title = "Edit Mahasiswa";
$currentPage = "edit_wisuda.php"; // Set halaman aktif sebagai kelola_mahasiswa
ob_start();
?>

<div class="page-content">
  <h2>Edit Data Mahasiswa</h2>

  <?php if ($data['is_edited'] == 1): ?>
    <div class="alert alert-warning">
      Data ini sudah pernah diedit dan tidak bisa diubah kembali.
    </div>
  <?php endif; ?>

  <div class="form-card">
    <form action="../../models/controllers/edit_proses.controller.php" method="POST">
      
      <!-- hidden wajib -->
      <input type="hidden" name="id_proses" value="<?= $data['id_proses']; ?>">
      <input type="hidden" name="id_mahasiswa" value="<?= $data['id_mahasiswa']; ?>">

      <div class="form-group">
        <label>Nama Mahasiswa</label>
        <input 
          type="text" 
          name="nama" 
          value="<?= htmlspecialchars($data['nama_mahasiswa']); ?>" 
          required
          <?= $data['is_edited'] ? 'readonly' : ''; ?>
        >
      </div>

      <div class="form-group">
        <label>NIM</label>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
          <span id="npm_format_display" style="background: #f0f0f0; padding: 0.75rem; border-radius: 4px; min-width: 80px; text-align: center; font-weight: bold;"><?= htmlspecialchars($prefix); ?></span>
          <input 
            type="text" 
            id="nim"
            name="nim" 
            value="<?= htmlspecialchars($suffix_nim); ?>" 
            required
            <?= $data['is_edited'] ? 'readonly' : ''; ?>
            style="flex: 1;"
          >
        </div>
      </div>

      <div class="form-group">
        <label>Program Studi</label>
        <select 
          name="id_prodi" 
          required 
          <?= $data['is_edited'] ? 'disabled' : ''; ?>
          onchange="updatePrefix()"
        >
          <?php
          $prodi_query = mysqli_query($conn, "
            SELECT p.id_prodi, p.nama_prodi, p.id_fakultas, f.npm_format 
            FROM prodi p 
            JOIN fakultas f ON p.id_fakultas = f.id_fakultas
            ORDER BY p.nama_prodi ASC
          ");
          while ($p = mysqli_fetch_assoc($prodi_query)) :
            $selected = ($p['id_prodi'] == $data['id_prodi']) ? "selected" : "";
          ?>
            <option value="<?= $p['id_prodi']; ?>" data-prefix="<?= htmlspecialchars($p['npm_format']); ?>" <?= $selected; ?>>
              <?= $p['nama_prodi']; ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-actions">
        <?php if ($data['is_edited'] == 0): ?>
          <button type="submit" name="edit_data" class="btn btn-primary">
            Edit Data
          </button>
        <?php endif; ?>

        <a href="kelola_mahasiswa.php" class="btn btn-secondary">
          Kembali
        </a>
      </div>

    </form>
  </div>
</div>

<script>
  function updatePrefix() {
    const prodiSelect = document.querySelector('select[name="id_prodi"]');
    const selectedOption = prodiSelect.options[prodiSelect.selectedIndex];
    const prefixDisplay = document.getElementById('npm_format_display');
    
    if (selectedOption) {
      const prefix = selectedOption.getAttribute('data-prefix');
      prefixDisplay.textContent = prefix || '-';
    }
  }
</script>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';

