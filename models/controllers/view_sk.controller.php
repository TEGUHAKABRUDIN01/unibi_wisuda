<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// Proteksi: hanya petugas yang boleh akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    $_SESSION['swal_error'] = 'Akses ditolak!';
    header("Location: /UNIBI_WISUDA/index.php");
    exit;
}

if (!isset($_GET['id_mahasiswa'])) {
    die("ID Mahasiswa tidak valid.");
}

$id_mahasiswa = mysqli_real_escape_string($conn, $_GET['id_mahasiswa']);

// Ambil SK dari database
$query = mysqli_query($conn, "SELECT sk_wisuda FROM mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'");
$data = mysqli_fetch_assoc($query);

if (!$data || empty($data['sk_wisuda'])) {
    die("SK Kelulusan tidak ditemukan.");
}

// Tampilkan sebagai PDF
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=SK_Wisuda_$id_mahasiswa.pdf");
echo $data['sk_wisuda'];
exit;