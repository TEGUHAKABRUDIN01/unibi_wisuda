<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (!isset($_POST['register_mahasiswa'])) {
  header("Location: /unibi_wisuda/views/mahasiswa/dashboard_mahasiswa.php");

  exit;
}

// Ambil input
$nama     = trim($_POST['nama']);
$nim      = trim($_POST['nim']);
$password = trim($_POST['password']);
$id_prodi = $_POST['id_prodi'];
$file     = $_FILES['sk_wisuda'];

// Validasi sederhana
if (empty($nama) || empty($nim) || empty($password) || empty($id_prodi) || empty($file['name'])) {
  $_SESSION['swal_error'] = "SEMUA FORM WAJIB DIISI!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// Validasi file PDF & ukuran
$file_ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$file_size = $file['size'];
$file_tmp  = $file['tmp_name'];

if ($file_ext !== 'pdf') {
  $_SESSION['swal_error'] = "FORMAT FILE HARUS PDF!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

if ($file_size > 5 * 1024 * 1024) {
  $_SESSION['swal_error'] = "Ukuran file terlalu besar (Maksimal 5MB)!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// Escape input
$nama     = mysqli_real_escape_string($conn, $nama);
$nim      = mysqli_real_escape_string($conn, $nim);
$password = mysqli_real_escape_string($conn, $password);
$file_sk  = addslashes(file_get_contents($file_tmp));

// Ambil id_fakultas dari id_prodi
$data_prodi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_fakultas FROM prodi WHERE id_prodi='$id_prodi'"));
if (!$data_prodi) {
  $_SESSION['swal_error'] = "Program Studi tidak ditemukan!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}
$id_fakultas = $data_prodi['id_fakultas'];
$id_akses    = 1;

mysqli_begin_transaction($conn);

try {
  // Insert mahasiswa
  $sql_mhs = "INSERT INTO mahasiswa 
                (id_prodi, id_fakultas, id_akses, nim, nama_mahasiswa, sk_wisuda, password)
                VALUES ('$id_prodi', '$id_fakultas', '$id_akses', '$nim', '$nama', '$file_sk', '$password')";
  mysqli_query($conn, $sql_mhs);
  $id_mahasiswa = mysqli_insert_id($conn);

  // Insert proses_wisuda
  mysqli_query($conn, "INSERT INTO proses_wisuda (id_mahasiswa, status_proses) VALUES ('$id_mahasiswa', 'proses')");

  mysqli_commit($conn);

$_SESSION['swal_konfirmasi'] = [
  'icon'  => 'success',
  'title' => 'Registrasi Berhasil',
  'text'  => 'Berhasil daftar, tunggu admin konfirmasi.'
];

header("Location: /UNIBI_WISUDA/views/mahasiswa/login_mahasiswa.php");
exit;

} catch (Exception $e) {
  mysqli_rollback($conn);
  $_SESSION['swal_error'] = "Terjadi kesalahan: " . mysqli_error($conn);
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}
