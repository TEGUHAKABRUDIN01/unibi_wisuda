<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (isset($_POST['login_mahasiswa'])) {
    $nim      = mysqli_real_escape_string($conn, trim($_POST['nim']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    $cek_mhs = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE nim = '$nim'");
    $data    = mysqli_fetch_assoc($cek_mhs);

    if (!$data) {
        $_SESSION['swal_error'] = "Login Gagal: NIM Mahasiswa tidak terdaftar! Silahkan registrasi.";
        header("Location: /unibi_wisuda/index.php");
        exit;
    }

    if ($data['password'] !== $password) {
        $_SESSION['swal_error'] = "Login Gagal: Password salah!";
        header("Location: /unibi_wisuda/index.php");
        exit;
    }

    if ($data['id_akses'] == 0) {
        $_SESSION['swal_error'] = "Login Gagal: Akun Anda belum di-ACC petugas.";
        header("Location: /unibi_wisuda/index.php");
        exit;
    }

    // Login berhasil
    $_SESSION['id_mahasiswa'] = $data['id_mahasiswa'];
    $_SESSION['nama']         = $data['nama_mahasiswa'];
    $_SESSION['role']         = 'mahasiswa';
    
    // Set SweetAlert sukses
    $_SESSION['swal_success'] = "Login berhasil! Selamat datang, " . $data['nama_mahasiswa'];
    
    header("Location: /unibi_wisuda/views/mahasiswa/dashboard_mahasiswa.php");
    exit;
}
