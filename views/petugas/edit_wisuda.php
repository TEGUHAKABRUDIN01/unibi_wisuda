<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

$id_proses = $_GET['id'];
// Ambil data lama
$query = mysqli_query($conn, "SELECT p.*, m.nama_mahasiswa, m.nim, m.id_prodi FROM proses_wisuda p 
                              JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa 
                              WHERE p.id_proses = '$id_proses'");
$data = mysqli_fetch_assoc($query);

// Jika sudah pernah diedit, tendang balik
if ($data['is_edited'] == 1) {
  echo "<script>alert('Data ini sudah pernah diedit dan tidak bisa diubah lagi!'); window.location='kelola_wisuda.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Edit Mahasiswa</title>
</head>

<body>
  <h2>Edit Data Mahasiswa</h2>
  <form action="../../models/controllers/edit_proses.controller.php" method="POST">
    <input type="hidden" name="id_proses" value="<?= $data['id_proses']; ?>">
    <input type="hidden" name="id_mahasiswa" value="<?= $data['id_mahasiswa']; ?>">

    <label>Nama Mahasiswa:</label><br>
    <input type="text" name="nama" value="<?= $data['nama_mahasiswa']; ?>" required><br><br>

    <label>NIM:</label><br>
    <input type="text" name="nim" value="<?= $data['nim']; ?>" required><br><br>

    <label>Program Studi:</label><br>
    <select name="id_prodi" required>
      <?php
      $prodi_query = mysqli_query($conn, "SELECT * FROM prodi");
      while ($p = mysqli_fetch_assoc($prodi_query)) {
        $selected = ($p['id_prodi'] == $data['id_prodi']) ? "selected" : "";
        echo "<option value='{$p['id_prodi']}' $selected>{$p['nama_prodi']}</option>";
      }
      ?>
    </select><br><br>

    <button type="submit" name="edit_data">EDIT DATA</button>
    <a href="kelola_wisuda.php">Batal</a>
  </form>
</body>

</html>