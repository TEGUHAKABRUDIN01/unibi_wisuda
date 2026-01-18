<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (isset($_POST['login_petugas'])) {

  $identifier = mysqli_real_escape_string($conn, trim($_POST['nim']));
  $password   = mysqli_real_escape_string($conn, trim($_POST['password']));

  $cek_ptg = mysqli_query($conn, "SELECT * FROM petugas WHERE nama_petugas='$identifier'");
  $data_ptg = mysqli_fetch_assoc($cek_ptg);

  // ❌ AKUN TIDAK DITEMUKAN
  if (!$data_ptg) {
    $_SESSION['swal_petugas'] = [
      'icon'  => 'error',
      'title' => 'Login Gagal',
      'text'  => 'Akun petugas tidak ditemukan!'
    ];
    header("Location: /UNIBI_WISUDA/views/petugas/login_petugas.php");
    exit;
  }

  // ❌ PASSWORD SALAH
  if ($data_ptg['password'] !== $password) {
    $_SESSION['swal_petugas'] = [
      'icon'  => 'error',
      'title' => 'Login Gagal',
      'text'  => 'Password petugas salah!'
    ];
    header("Location: /UNIBI_WISUDA/views/petugas/login_petugas.php");
    exit;
  }

  // ✅ LOGIN BERHASIL
  $_SESSION['id_petugas'] = $data_ptg['id_petugas'];
  $_SESSION['nama']      = $data_ptg['nama_petugas'];
  $_SESSION['role']      = 'petugas';

  $_SESSION['swal_success'] = [
    'icon'  => 'success',
    'title' => 'Login Berhasil',
    'text'  => 'Selamat datang, ' . $data_ptg['nama_petugas']
  ];

  header("Location: /UNIBI_WISUDA/views/petugas/dashboard_petugas.php");
  exit;
}
