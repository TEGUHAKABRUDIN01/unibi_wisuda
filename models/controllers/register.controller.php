<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

// Cek apakah diakses melalui tombol daftar
if (!isset($_POST['register_mahasiswa'])) {
  header("Location: /unibi_wisuda/views/mahasiswa/dashboard_mahasiswa.php");
  exit;
}

// 1. Ambil input dan bersihkan
$nama     = trim($_POST['nama']);
$nim      = trim($_POST['nim']);
$password = trim($_POST['password']);
$id_prodi = $_POST['id_prodi'];
$file     = $_FILES['sk_wisuda'];

// 2. VALIDASI DASAR: Form tidak boleh kosong
if (empty($nama) || empty($nim) || empty($password) || empty($id_prodi) || empty($file['name'])) {
  $_SESSION['swal_error'] = "SEMUA FORM WAJIB DIISI!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// 3. VALIDASI NIM: Harus angka & tepat 9 digit
if (!ctype_digit($nim) || strlen($nim) !== 9) {
  $_SESSION['swal_error'] = "NIM HARUS 9 DIGIT ANGKA!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// 4. VALIDASI: Cek apakah NIM sudah terdaftar sebelumnya
$cek_nim = mysqli_query($conn, "SELECT nim FROM mahasiswa WHERE nim = '$nim'");
if (mysqli_num_rows($cek_nim) > 0) {
  $_SESSION['swal_error'] = "NIM SUDAH TERDAFTAR!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// 5. VALIDASI: File harus PDF & Maksimal 5MB
$file_ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$file_size = $file['size'];
if ($file_ext !== 'pdf') {
  $_SESSION['swal_error'] = "FORMAT FILE HARUS PDF!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}
if ($file_size > 5 * 1024 * 1024) {
  $_SESSION['swal_error'] = "UKURAN FILE MAKSIMAL 5MB!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// 6. Persiapan Data (Escaping)
$nama     = mysqli_real_escape_string($conn, $nama);
$nim      = mysqli_real_escape_string($conn, $nim);
$password = mysqli_real_escape_string($conn, $password); // Disarankan password_hash jika sudah production
$file_sk  = addslashes(file_get_contents($file['tmp_name']));

// 7. Ambil id_fakultas berdasarkan id_prodi
$res_prodi = mysqli_query($conn, "SELECT id_fakultas FROM prodi WHERE id_prodi='$id_prodi'");
$data_prodi = mysqli_fetch_assoc($res_prodi);
if (!$data_prodi) {
  $_SESSION['swal_error'] = "PRODI TIDAK DITEMUKAN!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}
$id_fakultas = $data_prodi['id_fakultas'];

// ---------------------------------------------------------
// PROSES DATABASE DENGAN TRANSACTION
// ---------------------------------------------------------
mysqli_begin_transaction($conn);

try {
  // STEP 1: Insert ke tabel mahasiswa
  $sql_mhs = "INSERT INTO mahasiswa (id_prodi, id_fakultas, id_akses, nim, nama_mahasiswa, sk_wisuda, password)
                VALUES ('$id_prodi', '$id_fakultas', '1', '$nim', '$nama', '$file_sk', '$password')";
  if (!mysqli_query($conn, $sql_mhs)) {
    throw new Exception("Gagal simpan data mahasiswa: " . mysqli_error($conn));
  }
  $id_mahasiswa = mysqli_insert_id($conn);

  // STEP 2: Insert ke tabel proses_wisuda
  $sql_proses = "INSERT INTO proses_wisuda (id_mahasiswa, status_proses) VALUES ('$id_mahasiswa', 'proses')";
  if (!mysqli_query($conn, $sql_proses)) {
    throw new Exception("Gagal buat proses wisuda: " . mysqli_error($conn));
  }
  $id_proses = mysqli_insert_id($conn);

  // STEP 3: Insert ke tabel detail_wisuda
  // status_kehadiran otomatis 'tidak hadir' (default database)
  // id_barcode & id_kursi akan diisi nanti saat Admin ACC
  $sql_detail = "INSERT INTO detail_wisuda (id_proses) VALUES ('$id_proses')";
  if (!mysqli_query($conn, $sql_detail)) {
    throw new Exception("Gagal buat detail wisuda: " . mysqli_error($conn));
  }

  // JIKA SEMUA BERHASIL, BARU COMMIT
  mysqli_commit($conn);

  $_SESSION['swal_konfirmasi'] = [
    'icon'  => 'success',
    'title' => 'Berhasil',
    'text'  => 'Registrasi berhasil, silakan tunggu konfirmasi admin.'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/login_mahasiswa.php");
  exit;
} catch (Exception $e) {
  // JIKA ADA SATU SAJA YANG GAGAL, BATALKAN SEMUA (ROLLBACK)
  mysqli_rollback($conn);
  $_SESSION['swal_error'] = "Gagal Simpan: " . $e->getMessage();
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}
