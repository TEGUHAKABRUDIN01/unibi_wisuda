<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (isset($_POST['register_mahasiswa'])) {

  // Validasi Input Kosong
  $nama     = trim($_POST['nama']);
  $nim      = trim($_POST['nim']);
  $password = trim($_POST['password']);
  $id_prodi = $_POST['id_prodi'];

  if (empty($nama) || empty($nim) || empty($password) || empty($id_prodi) || empty($_FILES['sk_wisuda']['name'])) {
    echo "<script>alert('SEMUA FORM WAJIB DIISI!!'); window.history.back();</script>";
    exit;
  }

  // Validasi File Upload
  $file_name = $_FILES['sk_wisuda']['name'];
  $file_size = $_FILES['sk_wisuda']['size'];
  $file_tmp  = $_FILES['sk_wisuda']['tmp_name'];
  $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

  $allowed_ext = ['pdf'];
  $max_size    = 5 * 1024 * 1024; // 5 Megabytes

  if (!in_array($file_ext, $allowed_ext)) {
    echo "<script>alert('FORMAT FILE HARUS PDF!!!'); window.history.back();</script>";
    exit;
  }

  if ($file_size > $max_size) {
    echo "<script>alert('Ukuran file terlalu besar (Maksimal 5MB)!'); window.history.back();</script>";
    exit;
  }

  // Sanitasi Data
  $nama     = mysqli_real_escape_string($conn, $nama);
  $nim      = mysqli_real_escape_string($conn, $nim);
  $password = mysqli_real_escape_string($conn, $password);

  // Cari id_fakultas otomatis
  $query_prodi = mysqli_query($conn, "SELECT id_fakultas FROM prodi WHERE id_prodi = '$id_prodi'");
  $data_prodi  = mysqli_fetch_assoc($query_prodi);

  if (!$data_prodi) {
    echo "<script>alert('Program Studi tidak ditemukan!'); window.history.back();</script>";
    exit;
  }

  $id_fakultas = $data_prodi['id_fakultas'];
  $id_akses    = 1; // Default: Mahasiswa

  // Penyiapan Folder Upload
  $folder_upload = "../../uploads/";
  if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0777, true);
  }

  $new_file_name = "SK_" . $nim . "_" . time() . "." . $file_ext;
  $target_path   = $folder_upload . $new_file_name;

  // File Simpan Database
  if (move_uploaded_file($file_tmp, $target_path)) {

    mysqli_begin_transaction($conn);

    try {
      // Insert ke tabel mahasiswa
      $sql_mhs = "INSERT INTO mahasiswa (id_prodi, id_fakultas, id_akses, nim, nama_mahasiswa, sk_wisuda, password) 
                        VALUES ('$id_prodi', '$id_fakultas', '$id_akses', '$nim', '$nama', '$new_file_name', '$password')";
      mysqli_query($conn, $sql_mhs);

      $id_mahasiswa = mysqli_insert_id($conn);

      // Insert ke tabel proses_wisuda
      $sql_proses = "INSERT INTO proses_wisuda (id_mahasiswa, status_proses) 
                           VALUES ('$id_mahasiswa', 'proses')";
      mysqli_query($conn, $sql_proses);

      mysqli_commit($conn);

      echo "<script>alert('Registrasi Berhasil! Data Anda sedang diproses oleh petugas.'); window.location='/unibi_wisuda/index.php';</script>";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      if (file_exists($target_path)) {
        unlink($target_path); // Hapus file jika DB gagal
      }
      echo "<script>alert('Gagal menyimpan data ke database.'); window.history.back();</script>";
    }
  } else {
    echo "<script>alert('Gagal mengunggah file. Cek izin folder uploads!'); window.history.back();</script>";
  }
} else {
  header("Location: /unibi_wisuda/index.php");
  exit;
}
