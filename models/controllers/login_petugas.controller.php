<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (isset($_POST['login_petugas'])) {
  $identifier = mysqli_real_escape_string($conn, trim($_POST['nim']));
  $password   = mysqli_real_escape_string($conn, trim($_POST['password']));

  // --- LOGIKA KHUSUS PETUGAS ---
  $cek_ptg = mysqli_query($conn, "SELECT * FROM petugas WHERE nama_petugas = '$identifier'");
  $data_ptg = mysqli_fetch_assoc($cek_ptg);

  if (!$data_ptg) {
    echo "<script>alert('Login Gagal: Akun Petugas tidak ditemukan!'); window.history.back();</script>";
    exit;
  }

  if ($data_ptg['password'] !== $password) {
    echo "<script>alert('Login Gagal: Password Petugas salah!'); window.history.back();</script>";
    exit;
  }

  // Login Berhasil Petugas
  $_SESSION['id_petugas'] = $data_ptg['id_petugas'];
  $_SESSION['nama']    = $data_ptg['nama_petugas'];
  $_SESSION['role']    = 'petugas';
  echo "<script>alert('Selamat Datang Petugas!'); window.location='/UNIBI_WISUDA/views/petugas/dashboard_petugas.php';</script>";
}
