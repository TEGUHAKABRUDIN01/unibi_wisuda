<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

$id_proses = $_GET['id'] ?? null;
if (!$id_proses) {
  header("Location: kelola_wisuda.php");
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
    m.id_prodi
  FROM proses_wisuda p
  JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
  WHERE p.id_proses = '$id_proses'
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
  echo "<script>alert('Data tidak ditemukan'); window.location='kelola_wisuda.php';</script>";
  exit;
}

/* ===============================
   Buffer ke layout
================================ */
$title = "Edit Mahasiswa";
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
        <input 
          type="text" 
          name="nim" 
          value="<?= htmlspecialchars($data['nim']); ?>" 
          required
          <?= $data['is_edited'] ? 'readonly' : ''; ?>
        >
      </div>

      <div class="form-group">
        <label>Program Studi</label>
        <select 
          name="id_prodi" 
          required 
          <?= $data['is_edited'] ? 'disabled' : ''; ?>
        >
          <?php
          $prodi_query = mysqli_query($conn, "SELECT * FROM prodi");
          while ($p = mysqli_fetch_assoc($prodi_query)) :
            $selected = ($p['id_prodi'] == $data['id_prodi']) ? "selected" : "";
          ?>
            <option value="<?= $p['id_prodi']; ?>" <?= $selected; ?>>
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

        <a href="kelola_wisuda.php" class="btn btn-secondary">
          Kembali
        </a>
      </div>

    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
