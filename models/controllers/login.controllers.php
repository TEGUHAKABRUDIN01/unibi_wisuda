<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (isset($_POST['login_mahasiswa'])) {
  $nim      = trim($_POST['nim']);
  $password = trim($_POST['password']);

  // Validasi Input Kosong
  if (empty($nim) || empty($password)) {
    echo "<script>alert('NIM dan Password tidak boleh kosong!'); window.history.back();</script>";
    exit;
  }

  $nim      = mysqli_real_escape_string($conn, $nim);
  $password = mysqli_real_escape_string($conn, $password);

  // CEK NIM ADA DI DATABASE
  $cek_nim = mysqli_query($conn, "SELECT nim FROM mahasiswa WHERE nim = '$nim'");

  if (mysqli_num_rows($cek_nim) === 0) {
    echo "<script>alert('Login Gagal: NIM tidak terdaftar!'); window.history.back();</script>";
    exit;
  }

  // 3.  NIM, Password, ambil Status Proses
  $sql = "SELECT m.*, p.status_proses 
            FROM mahasiswa m 
            JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa 
            WHERE m.nim = '$nim' AND m.password = '$password' LIMIT 1";

  $result = mysqli_query($conn, $sql);
  $data   = mysqli_fetch_assoc($result);

  if ($data) {
    //  sudah di-ACC (Status Selesai)
    if ($data['status_proses'] !== 'selesai') {
      echo "<script>alert('Login Gagal: Akun Anda belum diverifikasi oleh Petugas/Admin.'); window.location='/unibi_wisuda/index.php';</script>";
      exit;
    }

    // Jika semua lolos, set session
    $_SESSION['id_mahasiswa']   = $data['id_mahasiswa'];
    $_SESSION['nama_mahasiswa'] = $data['nama_mahasiswa'];
    $_SESSION['id_akses']       = $data['id_akses'];
    $_SESSION['status_login']   = true;

    echo "<script>alert('Login Berhasil!'); window.location='/unibi_wisuda/views/mahasiswa/dashboard.php';</script>";
  } else {
    // Jika NIM ada tapi password salah
    echo "<script>alert('Login Gagal: Password salah!'); window.history.back();</script>";
  }
} else {
  header("Location: /unibi_wisuda/index.php");
  exit;
}
