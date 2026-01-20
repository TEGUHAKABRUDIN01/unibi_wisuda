<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (!isset($_POST['register_mahasiswa'])) {
  header("Location: /unibi_wisuda/views/mahasiswa/dashboard_mahasiswa.php");
  exit;
}

// 1. Ambil input dan bersihkan spasi di awal/akhir
$nama     = trim($_POST['nama']);
$nim      = trim($_POST['nim']);
$password = trim($_POST['password']);
$id_prodi = $_POST['id_prodi'];
$file     = $_FILES['sk_wisuda'];

// 2. VALIDASI: Form tidak boleh kosong
if (empty($nama) || empty($nim) || empty($password) || empty($id_prodi) || empty($file['name'])) {
  $_SESSION['swal_error'] = [
    'icon'  => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'Semua form wajib diisi!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}


// 3. VALIDASI: NIM harus angka
if (!ctype_digit($nim)) {
  $_SESSION['swal_error'] = [
    'icon'  => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'NIM hanya boleh berisi angka!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}



// 4. VALIDASI: Panjang NIM harus tepat 9 DIGIT
if (strlen($nim) !== 9) {
  $_SESSION['swal_error'] = [
    'icon'  => 'error',
    'title' => 'Registrasi Gagal',
    'text'  => 'NIM harus terdiri dari 9 digit!'
  ];
  header("Location: /UNIBI_WISUDA/views/mahasiswa/register.php");
  exit;
}


// 5. VALIDASI: Cek apakah NIM sudah terdaftar (Penting agar tidak duplikat)
$cek_nim_query = mysqli_query($conn, "SELECT nim FROM mahasiswa WHERE nim = '$nim'");
if (mysqli_num_rows($cek_nim_query) > 0) {
  $_SESSION['swal_error'] = "NIM SUDAH TERDAFTAR!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// 6. Validasi file PDF & ukuran
$file_ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$file_size = $file['size'];
$file_tmp  = $file['tmp_name'];

if ($file_ext !== 'pdf') {
  $_SESSION['swal_error'] = "FORMAT FILE HARUS PDF!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

if ($file_size > 5 * 1024 * 1024) {
  $_SESSION['swal_error'] = "UKURAN FILE TERLALU BESAR (MAKSIMAL 5MB)!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

// 7. Persiapan data untuk Database (Escape)
$nama     = mysqli_real_escape_string($conn, $nama);
$nim      = mysqli_real_escape_string($conn, $nim);
$password = mysqli_real_escape_string($conn, $password);
$file_sk  = addslashes(file_get_contents($file_tmp));

// 8. Ambil id_fakultas dari id_prodi
$res_prodi = mysqli_query($conn, "SELECT id_fakultas FROM prodi WHERE id_prodi='$id_prodi'");
$data_prodi = mysqli_fetch_assoc($res_prodi);

if (!$data_prodi) {
  $_SESSION['swal_error'] = "Program Studi tidak ditemukan!";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

$id_fakultas = $data_prodi['id_fakultas'];
$id_akses    = 1;

// 9. Eksekusi Database dengan Transaction
mysqli_begin_transaction($conn);

try {
  // Insert ke tabel mahasiswa
  $sql_mhs = "INSERT INTO mahasiswa 
                (id_prodi, id_fakultas, id_akses, nim, nama_mahasiswa, sk_wisuda, password)
                VALUES ('$id_prodi', '$id_fakultas', '$id_akses', '$nim', '$nama', '$file_sk', '$password')";
  mysqli_query($conn, $sql_mhs);

  $id_mahasiswa = mysqli_insert_id($conn);

  // Insert ke tabel proses_wisuda (status awal: proses)
  mysqli_query($conn, "INSERT INTO proses_wisuda (id_mahasiswa, status_proses) VALUES ('$id_mahasiswa', 'proses')");

  mysqli_commit($conn);

  $_SESSION['swal_konfirmasi'] = [
    'icon'  => 'success',
    'title' => 'Registrasi Berhasil',
    'text'  => 'Pendaftaran berhasil, silakan tunggu konfirmasi admin.'
  ];

  header("Location: /UNIBI_WISUDA/views/mahasiswa/login_mahasiswa.php");
  exit;
} catch (Exception $e) {
  mysqli_rollback($conn);
  $_SESSION['swal_error'] = "Terjadi kesalahan sistem: " . mysqli_error($conn);
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}
