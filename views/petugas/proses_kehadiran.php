<?php
// Pastikan tidak ada output apapun sebelum header JSON
ob_start();
include_once '../../config/config.php';
session_start();

header('Content-Type: application/json'); // Wajib memberi tahu browser ini JSON

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception("Metode pengiriman tidak valid.");
  }

  $raw_data = mysqli_real_escape_string($conn, $_POST['nim']);
  $id_petugas = $_SESSION['id_petugas'] ?? 1;

  $is_pendamping = (strpos($raw_data, 'PND-') === 0);
  $nim = $is_pendamping ? str_replace('PND-', '', $raw_data) : $raw_data;

  $sql = "SELECT p.id_proses, m.nama_mahasiswa 
            FROM proses_wisuda p
            JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
            WHERE m.nim = '$nim'";

  $result = mysqli_query($conn, $sql);
  $data = mysqli_fetch_assoc($result);

  if (!$data) {
    throw new Exception("Data tidak ditemukan untuk NIM: $nim");
  }

  $id_proses = $data['id_proses'];
  $nama = $data['nama_mahasiswa'];

  if ($is_pendamping) {
    $update = "UPDATE detail_wisuda SET status_kehadiran_pendamping = 'hadir', waktu_hadir_pendamping = NOW() WHERE id_proses = '$id_proses'";
    $msg = "PENDAMPING dari " . $nama;
  } else {
    $update = "UPDATE detail_wisuda SET status_kehadiran = 'hadir', timestamp_scan = NOW(), id_petugas = '$id_petugas' WHERE id_proses = '$id_proses'";
    $msg = "MAHASISWA: " . $nama;
  }

  if (mysqli_query($conn, $update)) {
    ob_clean(); // Hapus output buffer jika ada peringatan/warning PHP yang terselip
    echo json_encode(['status' => 'success', 'nama' => $msg]);
  } else {
    throw new Exception("Gagal update database: " . mysqli_error($conn));
  }
} catch (Exception $e) {
  ob_clean();
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;
