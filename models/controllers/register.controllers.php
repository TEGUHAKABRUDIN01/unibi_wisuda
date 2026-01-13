<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (isset($_POST['register_mahasiswa'])) {

  // validasi input kosong
  $nama     = trim($_POST['nama']);
  $nim      = trim($_POST['nim']);
  $password = trim($_POST['password']);
  $id_prodi = $_POST['id_prodi'];

  if (empty($nama) || empty($nim) || empty($password) || empty($id_prodi) || empty($_FILES['sk_wisuda']['name'])) {
    echo "<script>alert('SEMUA FORM WAJIB DIISI!!'); window.history.back();</script>";
    exit;
  }

  // 2. valid file tipe pdf dan ukuran max 5 mb
  $file_name = $_FILES['sk_wisuda']['name'];
  $file_size = $_FILES['sk_wisuda']['size'];
  $file_tmp  = $_FILES['sk_wisuda']['tmp_name'];
  $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

  if ($file_ext !== 'pdf') {
    echo "<script>alert('FORMAT FILE HARUS PDF!!!'); window.history.back();</script>";
    exit;
  }

  if ($file_size > 5 * 1024 * 1024) {
    echo "<script>alert('Ukuran file terlalu besar (Maksimal 5MB)!'); window.history.back();</script>";
    exit;
  }

  // persiapan file ke binary
  $nama     = mysqli_real_escape_string($conn, $nama);
  $nim      = mysqli_real_escape_string($conn, $nim);
  $password = mysqli_real_escape_string($conn, $password);

  // Membaca file menjadi data biner untuk disimpan di database (BLOB)
  $file_sk = addslashes(file_get_contents($file_tmp));

  // cari id_fakultas otomatis
  $query_prodi = mysqli_query($conn, "SELECT id_fakultas FROM prodi WHERE id_prodi = '$id_prodi'");
  $data_prodi  = mysqli_fetch_assoc($query_prodi);

  if (!$data_prodi) {
    echo "<script>alert('Program Studi tidak ditemukan!'); window.history.back();</script>";
    exit;
  }

  $id_fakultas = $data_prodi['id_fakultas'];
  $id_akses    = 1;

  mysqli_begin_transaction($conn);

  try {
    // Insert ke tabel mahasiswa (File masuk ke kolom sk_wisuda)
    $sql_mhs = "INSERT INTO mahasiswa (id_prodi, id_fakultas, id_akses, nim, nama_mahasiswa, sk_wisuda, password) 
                    VALUES ('$id_prodi', '$id_fakultas', '$id_akses', '$nim', '$nama', '$file_sk', '$password')";
    mysqli_query($conn, $sql_mhs);

    $id_mahasiswa = mysqli_insert_id($conn);

    // Insert ke tabel proses_wisuda
    $sql_proses = "INSERT INTO proses_wisuda (id_mahasiswa, status_proses) 
                       VALUES ('$id_mahasiswa', 'proses')";
    mysqli_query($conn, $sql_proses);

    mysqli_commit($conn);

    echo "<script>alert('Registrasi Berhasil! Akun Anda non-aktif sementara sampai diverifikasi oleh petugas.'); window.location='/unibi_wisuda/index.php';</script>";
  } catch (Exception $e) {
    mysqli_rollback($conn);
    // Tampilkan pesan error asli dari database
    echo "Detail Error: " . mysqli_error($conn) . " | " . $e->getMessage();
    exit;
  }
} else {
  header("Location: /unibi_wisuda/index.php");
  exit;
}
