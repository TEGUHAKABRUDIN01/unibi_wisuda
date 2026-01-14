<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (isset($_POST['login_mahasiswa'])) {
  $identifier = mysqli_real_escape_string($conn, trim($_POST['nim']));
  $password   = mysqli_real_escape_string($conn, trim($_POST['password']));

  // --- LOGIKA KHUSUS MAHASISWA ---
  $cek_mhs = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE nim = '$identifier'");
  $data = mysqli_fetch_assoc($cek_mhs);

  if (!$data) {
    echo "<script>alert('Login Gagal: NIM Mahasiswa tidak terdaftar! Silahkan registrasi terlebih dahulu.'); window.history.back();</script>";
    exit;
  }

  if ($data['password'] !== $password) {
    echo "<script>alert('Login Gagal: Password Mahasiswa salah!'); window.history.back();</script>";
    exit;
  }

  if ($data['id_akses'] == 0) {
    echo "<script>alert('Login Gagal: Akun Mahasiswa belum di-ACC oleh Petugas.'); window.history.back();</script>";
    exit;
  }

  // Login Berhasil Mahasiswa
  $_SESSION['id_user'] = $data['id_mahasiswa'];
  $_SESSION['nama']    = $data['nama_mahasiswa'];
  $_SESSION['role']    = 'mahasiswa';
  echo "<script>alert('Selamat Datang Mahasiswa!'); window.location='/UNIBI_WISUDA/views/mahasiswa/dashboard_mahasiswa.php';</script>";
}
