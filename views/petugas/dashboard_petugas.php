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

echo "Total Mahasiwa: " . $total_wisudawan . "<br>";
echo "Menunggu Konfirmasi:" . $data_proses['total_proses'] . "<br>";
echo "Berhasil Konfirmasi:" . $data_selesai['total_selesai'] . "<br>";
