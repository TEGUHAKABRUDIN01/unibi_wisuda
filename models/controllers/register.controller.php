<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

// Cek apakah diakses melalui tombol daftar
if (!isset($_POST['register_mahasiswa'])) {
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// Simpan semua input ke session agar data tidak hilang jika ada error
$_SESSION['form_data'] = $_POST;
$_SESSION['error_fields'] = [];

// 1. Ambil input
$nama     = trim($_POST['nama']);
$nim      = trim($_POST['nim']);
$password = trim($_POST['password']);
$id_prodi = $_POST['id_prodi'];
$file     = $_FILES['sk_wisuda'];

// 2. Validasi dasar: Cek form kosong
if (empty($nama)) $_SESSION['error_fields'][] = 'nama';
if (empty($nim)) $_SESSION['error_fields'][] = 'nim';
if (empty($password)) $_SESSION['error_fields'][] = 'password';
if (empty($id_prodi)) $_SESSION['error_fields'][] = 'id_prodi';
if (empty($file['name'])) $_SESSION['error_fields'][] = 'sk_wisuda';

if (!empty($_SESSION['error_fields'])) {
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'Semua form wajib diisi!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 3. Validasi Nama: tidak boleh mengandung angka
if (preg_match('/[0-9]/', $nama)) {
  $_SESSION['error_fields'][] = 'nama';
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'Nama lengkap tidak boleh mengandung angka!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 4. Validasi NIM (harus angka & 9 digit)
if (!ctype_digit($nim) || strlen($nim) !== 9) {
  $_SESSION['error_fields'][] = 'nim';
  $_SESSION['form_data']['nim'] = '';
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'NIM harus 9 digit angka!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 4a. Validasi NIM duplikat
$cek_nim = mysqli_query($conn, "SELECT nim FROM mahasiswa WHERE nim = '$nim'");
if (mysqli_num_rows($cek_nim) > 0) {
  $_SESSION['error_fields'][] = 'nim';
  $_SESSION['form_data']['nim'] = '';
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'NIM sudah terdaftar!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 5. Validasi Password (Huruf, Angka, Max 16 Karakter)
if (strlen($password) > 16) {
  $_SESSION['error_fields'][] = 'password';
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'Password maksimal 16 karakter!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
  $_SESSION['error_fields'][] = 'password';
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'Password harus mengandung huruf dan angka!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 6. Validasi file PDF
$file_ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($file_ext !== 'pdf') {
  $_SESSION['error_fields'][] = 'sk_wisuda';
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'Format file harus PDF!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}

// 7. Escape data & Ambil Data Pendukung
$nama     = mysqli_real_escape_string($conn, $nama);
$nim      = mysqli_real_escape_string($conn, $nim);
$password = mysqli_real_escape_string($conn, $password);
$file_sk  = addslashes(file_get_contents($file['tmp_name']));

$res_prodi = mysqli_query($conn, "SELECT id_fakultas FROM prodi WHERE id_prodi='$id_prodi'");
$data_prodi = mysqli_fetch_assoc($res_prodi);
if (!$data_prodi) {
  $_SESSION['error_fields'][] = 'id_prodi';
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'Program studi tidak ditemukan!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}
$id_fakultas = $data_prodi['id_fakultas'];

// ---------------------------------------------------------
// PROSES DATABASE (TRANSACTION)
// ---------------------------------------------------------
mysqli_begin_transaction($conn);

try {
  // STEP 1: Insert ke tabel mahasiswa
  $sql_mhs = "INSERT INTO mahasiswa (id_prodi, id_fakultas, id_akses, nim, nama_mahasiswa, sk_wisuda, password)
                VALUES ('$id_prodi', '$id_fakultas', '1', '$nim', '$nama', '$file_sk', '$password')";
  if (!mysqli_query($conn, $sql_mhs)) {
    throw new Exception("Error mahasiswa: " . mysqli_error($conn));
  }
  $id_mahasiswa = mysqli_insert_id($conn);

  // STEP 2: Insert ke tabel proses_wisuda
  $sql_proses = "INSERT INTO proses_wisuda (id_mahasiswa, status_proses) VALUES ('$id_mahasiswa', 'proses')";
  if (!mysqli_query($conn, $sql_proses)) {
    throw new Exception("Error proses_wisuda: " . mysqli_error($conn));
  }
  $id_proses = mysqli_insert_id($conn);

  // STEP 3: Insert ke tabel detail_wisuda
  // Menggunakan NULL eksplisit sesuai gambar struktur tabel Anda
  $sql_detail = "INSERT INTO detail_wisuda (id_proses, id_barcode, id_kursi, status_kehadiran) 
                   VALUES ('$id_proses', NULL, NULL, 'proses')";
  if (!mysqli_query($conn, $sql_detail)) {
    throw new Exception("Error detail_wisuda: " . mysqli_error($conn));
  }

  // JIKA SEMUA BERHASIL, BARU COMMIT
  mysqli_commit($conn);

  // Bersihkan data session agar form kembali kosong
  unset($_SESSION['form_data']);
  unset($_SESSION['error_fields']);

  // Notifikasi sukses
  $_SESSION['swal_konfirmasi'] = [
    'icon'  => 'success',
    'title' => 'Registrasi Berhasil',
    'text'  => 'Pendaftaran berhasil, silakan login untuk cek status.'
  ];

  // REDIRECT KE HALAMAN LOGIN
  header("Location: /UNIBI_WISUDA/views/mahasiswa/login_mahasiswa.php");
  exit;
} catch (Exception $e) {
  // Batalkan semua jika salah satu gagal
  mysqli_rollback($conn);
  $_SESSION['swal_error'] = [
    'icon' => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => $e->getMessage()
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}
