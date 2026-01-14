<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (isset($_POST['login_mahasiswa'])) {
  $identifier = mysqli_real_escape_string($conn, trim($_POST['nim']));
  $password   = mysqli_real_escape_string($conn, trim($_POST['password']));

  // 1. Cek apakah ini format NIM (Mahasiswa) atau Nama (Petugas)
  // Asumsi: Jika input diawali angka, kita anggap itu NIM Mahasiswa
  $is_numeric = is_numeric($identifier);

  if ($is_numeric) {
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
  } else {
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
}
