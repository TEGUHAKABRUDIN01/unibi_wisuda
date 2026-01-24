<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (!isset($_POST['register_mahasiswa'])) {
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 1. Ambil input
$nama     = trim($_POST['nama']);
$nim      = trim($_POST['nim']);
$password = trim($_POST['password']);
$id_prodi = $_POST['id_prodi'];
$file     = $_FILES['sk_wisuda'];

// 2. Validasi dasar
if (empty($nama) || empty($nim) || empty($password) || empty($id_prodi) || empty($file['name'])) {
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text' => 'Semua form wajib diisi!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 3. Validasi NIM
if (!ctype_digit($nim) || strlen($nim) !== 9) {
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text' => 'NIM harus 9 digit angka!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 4. Validasi file PDF
$file_ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($file_ext !== 'pdf') {
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text' => 'Format file harus PDF!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 5. Escape data
$nama     = mysqli_real_escape_string($conn, $nama);
$nim      = mysqli_real_escape_string($conn, $nim);
$password = mysqli_real_escape_string($conn, $password);
$file_sk  = addslashes(file_get_contents($file['tmp_name']));

// 6. Ambil id_fakultas
$res_prodi = mysqli_query($conn, "SELECT id_fakultas FROM prodi WHERE id_prodi='$id_prodi'");
$data_prodi = mysqli_fetch_assoc($res_prodi);
if (!$data_prodi) {
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text' => 'Program studi tidak ditemukan!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}
$id_fakultas = $data_prodi['id_fakultas'];

// ---------------------------------------------------------
// PROSES DATABASE
// ---------------------------------------------------------
mysqli_begin_transaction($conn);

try {
  // 1. Simpan Data Mahasiswa
  $sql_mhs = "INSERT INTO mahasiswa 
              (id_prodi, id_fakultas, id_akses, nim, nama_mahasiswa, sk_wisuda, password)
              VALUES ('$id_prodi', '$id_fakultas', '1', '$nim', '$nama', '$file_sk', '$password')";
  if (!mysqli_query($conn, $sql_mhs)) {
    throw new Exception("Error mahasiswa: " . mysqli_error($conn));
  }
  $id_mahasiswa = mysqli_insert_id($conn);

  // 2. Simpan ke Proses Wisuda
  $sql_proses = "INSERT INTO proses_wisuda (id_mahasiswa, status_proses) VALUES ('$id_mahasiswa', 'proses')";
  if (!mysqli_query($conn, $sql_proses)) {
    throw new Exception("Error proses_wisuda: " . mysqli_error($conn));
  }
  $id_proses = mysqli_insert_id($conn);

  // 3. Shadowing ke Detail Wisuda (isi id_barcode & id_kursi dengan NULL)
  $sql_detail = "INSERT INTO detail_wisuda (id_proses, id_barcode, id_kursi, status_kehadiran) 
                 VALUES ('$id_proses', NULL, NULL, 'tidak hadir')";
  if (!mysqli_query($conn, $sql_detail)) {
    throw new Exception("Error detail_wisuda: " . mysqli_error($conn));
  }

  // ✅ Commit sekali saja
  mysqli_commit($conn);

  // ✅ Notifikasi sukses
  $_SESSION['swal_konfirmasi'] = [
    'icon'  => 'success',
    'title' => 'Registrasi Berhasil',
    'text'  => 'Pendaftaran berhasil, silakan tunggu konfirmasi admin.'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/login_mahasiswa.php");
  exit;

} catch (Exception $e) {
  mysqli_rollback($conn);
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text' => $e->getMessage()
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}