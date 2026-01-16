<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// Pastikan hanya mahasiswa yang bisa akses
if (!isset($_SESSION['id_mahasiswa'])) {
  header("Location: ../../index.php");
  exit;
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];

// Ambil data mahasiswa untuk ditampilkan di input (Read-Only)
$query = mysqli_query($conn, "SELECT nim, nama_mahasiswa FROM mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'");
$mhs = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Form Data Orang Tua / Pendamping</title>
</head>

<body>

  <div class="form-container">
    <h2>Data Pendamping Wisuda</h2>
    <form action="../../models/controllers/simpan_pendamping.controller.php" method="POST">

      <div class="form-group">
        <label>NIM Mahasiswa</label>
        <input type="text" value="<?= $mhs['nim']; ?>" readonly>
      </div>

      <div class="form-group">
        <label>Nama Mahasiswa</label>
        <input type="text" value="<?= $mhs['nama_mahasiswa']; ?>" readonly>
      </div>

      <hr>

      <div class="form-group">
        <label>Nama Orang Tua 1 (Ayah/Wali)</label>
        <input type="text" name="nama_ayah" placeholder="Masukkan nama lengkap" required>
      </div>

      <div class="form-group">
        <label>Nama Orang Tua 2 (Ibu/Wali)</label>
        <input type="text" name="nama_ibu" placeholder="Masukkan nama lengkap" required>
      </div>

      <button type="submit" class="btn-simpan">Simpan Data Pendamping</button>
      <p class="note">*Data ini akan tercetak pada kartu wisuda Anda.</p>
    </form>
  </div>

</body>

</html>