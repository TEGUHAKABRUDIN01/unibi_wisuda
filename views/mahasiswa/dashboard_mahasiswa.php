<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// 1. Cek apakah user sudah login
if (!isset($_SESSION['id_mahasiswa'])) {
  echo "<script>alert('Sesi Berakhir, Silakan Login Kembali'); window.location='../../index.php';</script>";
  exit;
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];

// 2. Ambil data mahasiswa dengan JOIN ke prodi
$sql = "SELECT m.nim, m.nama_mahasiswa, pr.nama_prodi, p.status_proses
        FROM mahasiswa m
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        LEFT JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        WHERE m.id_mahasiswa = '$id_mahasiswa'";

$query = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($query);

// 3. Validasi jika data tidak ditemukan agar tidak error Offset on Null
if (!$data) {
  die("Error: Data profil mahasiswa tidak ditemukan di database.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Dashboard Mahasiswa</title>
</head>

<body>

  <div class="container">

    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>NIM</th>
          <th>Nama</th>
          <th>Studi Program</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td><?= htmlspecialchars($data['nim']); ?></td>
          <td><?= htmlspecialchars($data['nama_mahasiswa']); ?></td>
          <td><?= htmlspecialchars($data['nama_prodi']); ?></td>
          <td>
            <?php if (isset($data['status_proses']) && $data['status_proses'] == 'selesai'): ?>
              <a href="../../models/controllers/generate_kartu.controller.php" class="btn-unduh" target="_blank">
                UNDUH PDF
              </a>
            <?php else: ?>
              <span class="status-badge">Menunggu Konfirmasi</span>
            <?php endif; ?>
          </td>
        </tr>
      </tbody>
    </table>

    <a href="form_pendamping.php">Form Pendamping</a>
  </div>

</body>

</html>