<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  $_SESSION['swal_error'] = 'Akses ditolak!';
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

if (!isset($_GET['id_proses'])) {
  $_SESSION['swal_error'] = 'ID proses tidak valid!';
  header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
  exit;
}

$id_proses = mysqli_real_escape_string($conn, $_GET['id_proses']);

$query_cari = mysqli_query(
  $conn,
  "SELECT id_mahasiswa FROM proses_wisuda WHERE id_proses = '$id_proses'"
);
$data = mysqli_fetch_assoc($query_cari);

if (!$data) {
  $_SESSION['swal_error'] = 'Data tidak ditemukan!';
  header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
  exit;
}

$id_mahasiswa = $data['id_mahasiswa'];

mysqli_begin_transaction($conn);

try {

  mysqli_query($conn, "DELETE FROM proses_wisuda WHERE id_proses = '$id_proses'");
  mysqli_query($conn, "DELETE FROM mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'");

  mysqli_commit($conn);

  $_SESSION['swal_success'] = 'Data mahasiswa dan wisuda berhasil dihapus!';
  header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
  exit;

} catch (Exception $e) {
  mysqli_rollback($conn);
  $_SESSION['swal_error'] = 'Gagal menghapus data!';
  header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
  exit;
}
