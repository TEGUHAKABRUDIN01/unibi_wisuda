<?php
ob_start();
include_once '../../config/config.php';
session_start();

header('Content-Type: application/json');

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception("Metode pengiriman tidak valid.");
  }

  $raw_data = mysqli_real_escape_string($conn, $_POST['nim']);
  $id_petugas = $_SESSION['id_petugas'] ?? 1;

  $is_pendamping = (strpos($raw_data, 'PND-') === 0);
  $nim = $is_pendamping ? str_replace('PND-', '', $raw_data) : $raw_data;

  // Ambil data lengkap mahasiswa + kursi + prodi + fakultas
  $sql = "SELECT p.id_proses, m.nama_mahasiswa, pr.nama_prodi, f.nama_fakultas,
                 k.no_kursi, k.no_kursi_p1, k.no_kursi_p2
          FROM proses_wisuda p
          JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
          JOIN prodi pr ON m.id_prodi = pr.id_prodi
          LEFT JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
          LEFT JOIN kursi k ON p.id_proses = k.id_proses
          WHERE m.nim = '$nim'";

  $result = mysqli_query($conn, $sql);
  $data = mysqli_fetch_assoc($result);

  if (!$data) {
    throw new Exception("Data tidak ditemukan untuk NIM: $nim");
  }

  $id_proses = $data['id_proses'];
  $nama      = $data['nama_mahasiswa'];
  $prodi     = $data['nama_prodi'];
  $fakultas  = $data['nama_fakultas'];
  $no_kursi  = $data['no_kursi'];
  $no_kursi_p1 = $data['no_kursi_p1'];
  $no_kursi_p2 = $data['no_kursi_p2'];

  if ($is_pendamping) {
    $update = "UPDATE detail_wisuda 
                  SET status_kehadiran_pendamping = 'hadir', 
                      waktu_hadir_pendamping = NOW() 
                WHERE id_proses = '$id_proses'";
    $role = "pendamping";
  } else {
    $update = "UPDATE detail_wisuda 
                  SET status_kehadiran = 'hadir', 
                      timestamp_scan = NOW(), 
                      id_petugas = '$id_petugas' 
                WHERE id_proses = '$id_proses'";
    $role = "mahasiswa";
  }

  if (mysqli_query($conn, $update)) {
    ob_clean();
    echo json_encode([
      'status' => 'success',
      'role' => $role,
      'nama' => $nama,
      'prodi' => $prodi,
      'fakultas' => $fakultas,
      'no_kursi' => $no_kursi,
      'no_kursi_p1' => $no_kursi_p1,
      'no_kursi_p2' => $no_kursi_p2
    ]);
  } else {
    throw new Exception("Gagal update database: " . mysqli_error($conn));
  }
} catch (Exception $e) {
  ob_clean();
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;