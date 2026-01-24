<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (!isset($_POST['register_mahasiswa'])) {
  header("Location: /unibi_wisuda/views/mahasiswa/dashboard_mahasiswa.php");
  exit;
}

// 1. Ambil input
$nama     = trim($_POST['nama']);
$nim      = trim($_POST['nim']);
$password = trim($_POST['password']);
$id_prodi = $_POST['id_prodi'];
$file     = $_FILES['sk_wisuda'];

// 2. VALIDASI DASAR (Sesuai kode Anda)
if (empty($nama) || empty($nim) || empty($password) || empty($id_prodi) || empty($file['name'])) {
  $_SESSION['swal_error'] = "SEMUA FORM WAJIB DIISI!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// 3. VALIDASI NIM (Angka & 9 Digit)
if (!ctype_digit($nim) || strlen($nim) !== 9) {
  $_SESSION['swal_error'] = "NIM HARUS 9 DIGIT ANGKA!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// 4. Validasi file PDF
$file_ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($file_ext !== 'pdf') {
  $_SESSION['swal_error'] = "FORMAT FILE HARUS PDF!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// Persiapan data
$nama     = mysqli_real_escape_string($conn, $nama);
$nim      = mysqli_real_escape_string($conn, $nim);
$password = mysqli_real_escape_string($conn, $password);
$file_sk  = addslashes(file_get_contents($file['tmp_name']));

// Ambil id_fakultas
$res_prodi = mysqli_query($conn, "SELECT id_fakultas FROM prodi WHERE id_prodi='$id_prodi'");
$data_prodi = mysqli_fetch_assoc($res_prodi);
$id_fakultas = $data_prodi['id_fakultas'];

// ---------------------------------------------------------
// PROSES DATABASE
// ---------------------------------------------------------
mysqli_begin_transaction($conn);

try {
  // 1. Simpan Data Mahasiswa
  $sql_mhs = "INSERT INTO mahasiswa (id_prodi, id_fakultas, id_akses, nim, nama_mahasiswa, sk_wisuda, password)
              VALUES ('$id_prodi', '$id_fakultas', '1', '$nim', '$nama', '$file_sk', '$password')";
  mysqli_query($conn, $sql_mhs);
  $id_mahasiswa = mysqli_insert_id($conn);

  // 2. Simpan ke Proses Wisuda
  $sql_proses = "INSERT INTO proses_wisuda (id_mahasiswa, status_proses) VALUES ('$id_mahasiswa', 'proses')";
  mysqli_query($conn, $sql_proses);
  $id_proses = mysqli_insert_id($conn);

  // 3. Simpan ke Detail Wisuda (Sekarang Berhasil karena sudah boleh NULL)
  // status_kehadiran otomatis menjadi 'tidak hadir' sesuai default di database
  $sql_detail = "INSERT INTO detail_wisuda (id_proses) VALUES ('$id_proses')";
  mysqli_query($conn, $sql_detail);

  mysqli_commit($conn);

  if (!mysqli_query($conn, $sql_detail)) {
    throw new Exception(mysqli_error($conn));
  }

  mysqli_commit($conn);

  $_SESSION['swal_konfirmasi'] = [
    'icon'  => 'success',
    'title' => 'Berhasil',
    'text'  => 'Registrasi berhasil!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/login_mahasiswa.php");
  exit;
} catch (Exception $e) {
  mysqli_rollback($conn);
  // Tampilkan error asli untuk debug
  $_SESSION['swal_error'] = "Gagal Simpan: " . $e->getMessage();
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}
