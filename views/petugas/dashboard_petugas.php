<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// hanya petugas yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

// hitung total wisudawan status = 'proses'
$query_proses = mysqli_query($conn, "SELECT COUNT(*) as total_proses FROM proses_wisuda WHERE status_proses = 'proses'");
$data_proses = mysqli_fetch_assoc($query_proses);

// hitung total wisudawan status = 'selesai'
$query_selesai = mysqli_query($conn, "SELECT COUNT(*) AS total_selesai FROM proses_wisuda WHERE status_proses = 'selesai'");
$data_selesai = mysqli_fetch_assoc($query_selesai);

// menghitung total wisudawan
$total_wisudawan = $data_proses['total_proses'] + $data_selesai['total_selesai'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard Petugas</title>
</head>
<body>

<!-- Tombol Logout di atas tabel -->
<form method="POST" action="/UNIBI_WISUDA/models/controllers/logout.controller.php">
  <button type="submit">Logout</button>
</form>

<h2>Data Wisudawan</h2>
<table border="1" cellpadding="5" cellspacing="0">
  <tr>
    <th>Total Mahasiswa</th>
    <th>Menunggu Konfirmasi</th>
    <th>Berhasil Konfirmasi</th>
  </tr>
  <tr>
    <td><?php echo $total_wisudawan; ?></td>
    <td><?php echo $data_proses['total_proses']; ?></td>
    <td><?php echo $data_selesai['total_selesai']; ?></td>
  </tr>
</table>

<br>
<a href="/UNIBI_WISUDA/views/petugas/kelola_wisuda.php">Kelola Wisudawan</a>

</body>
</html>